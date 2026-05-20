<script setup>
import ListUser from "../components/ListUser.vue";
import {
  ref,
  onBeforeMount,
  onBeforeUnmount,
  inject,
  computed,
  watch,
} from "vue";
import { useRoute } from "vue-router";
import Chat from "../components/Chat.vue";
import axios from "axios";

const route = useRoute();
const currentRoom = ref({});
const selectedUser = ref(null);
const usersOnline = ref([]);
const filteredUsers = ref([]);
const privateRoomId = ref(null);
const rooms = inject("$rooms");
const user = inject("$user");
const roomId = inject("$roomId");
const appName = inject("$appName");
const showToast = inject("$showToast");

// Fetch allowed user roles based on current user's role
const fetchAllowedUsers = async () => {
  try {
    const response = await axios.get('/chat-users');
    filteredUsers.value = response.data;
  } catch (error) {
    console.error('Error fetching allowed users:', error);
  }
};

// Filter online users by allowed roles
const getFilteredOnlineUsers = () => {
  if (filteredUsers.value.length === 0) {
    return usersOnline.value;
  }

  // Normalize to numbers — Pusher presence channel may return string IDs
  const allowedIds = filteredUsers.value.map(u => Number(u.id));
  return usersOnline.value.filter(onlineUser =>
    allowedIds.includes(Number(onlineUser.id)) || Number(onlineUser.id) === Number(user.id)
  );
};

onBeforeMount(async () => {
  await fetchAllowedUsers();

  // Global presence channel — tracks ALL online users regardless of which room they're in
  Echo.join('online')
    .here((users) => {
      usersOnline.value = users.map(u => ({ ...u, new_messages: u.new_messages ?? 0 }));
    })
    .joining((joinedUser) => {
      usersOnline.value.push({ ...joinedUser, new_messages: 0 });
      if (selectedUser.value && Number(joinedUser.id) === Number(selectedUser.value.id)) {
        selectedUser.value.isOnline = true;
      }
    })
    .leaving((leftUser) => {
      const idx = usersOnline.value.findIndex(
        (item) => Number(item.id) === Number(leftUser.id)
      );
      if (idx > -1) {
        usersOnline.value.splice(idx, 1);
      }
      if (selectedUser.value && Number(leftUser.id) === Number(selectedUser.value.id)) {
        selectedUser.value.isOnline = false;
      }
    });

  const index = rooms.findIndex(
    (item) => item.id === parseInt(roomId)
  );
  if (index > -1) {
    currentRoom.value = rooms[index];

    // Room-specific channel — only for group chat messages
    Echo.join(`room.${currentRoom.value.id}`);

    // Listen for private messages sent directly to this user
    Echo.private(`room.${user.id}`).listen("MessagePosted", (e) => {
      if (!selectedUser.value) {
        const idx = usersOnline.value.findIndex(
          (item) => Number(item.id) === Number(e.message.user.id)
        );
        if (idx > -1) {
          usersOnline.value[idx].new_messages = (usersOnline.value[idx].new_messages || 0) + 1;
        }
      }
    });
  }
});

onBeforeUnmount(() => {
  Echo.leave('online');
  Echo.leave(`room.${currentRoom.value.id}`);
});

async function selectReceiver(receiver) {
  if (receiver.id === user.id) {
    showToast("Error", "You can't chat with yourself");
    return;
  }

  if (selectedUser.value) {
    showToast("Error", "You can only chat with one user at a time");
    return
  }

  try {
    const response = await axios.post(`/start_chat`, {
      receiver_id: receiver.id,
    });

    privateRoomId.value = response.data.id

    // Get full user data including phone from filteredUsers
    const fullUserData = filteredUsers.value.find(u => u.id === receiver.id) || receiver;

    selectedUser.value = {
      ...receiver,
      ...fullUserData, // Merge to include phone and other fields
      isOnline: usersOnline.value.find(item => item.id === receiver.id),
    };

    const onlineUser = usersOnline.value.find((item) => item.id === receiver.id);
    if (onlineUser) {
      onlineUser.new_messages = 0;
    }
  } catch (error) {
    console.error(error);
  }
}

function closeChat() {
  selectedUser.value = null;
  privateRoomId.value = null;
}

// Computed property: return filtered online users based on role
const displayUsers = computed(() => {
  return getFilteredOnlineUsers();
});

const totalUnreadPrivateMessages = computed(() => {
  let count = 0;
  displayUsers.value.forEach((item) => {
    count += item.new_messages;
  });
  return count;
});

watch(totalUnreadPrivateMessages, () => {
  if (totalUnreadPrivateMessages.value > 0) {
    document.title = `${totalUnreadPrivateMessages.value > 0
        ? "(" + totalUnreadPrivateMessages.value + ")"
        : ""
      } - ${appName}`;
  } else {
    document.title = appName;
  }
});
</script>

<template>
  <div class="flex h-100" style="height:100%;">
    <div class="row h-100 g-3" style="height:100%;">
      <div class="col-md-8 d-flex flex-column" style="height:100%;">
        <Chat :roomId="currentRoom.id" :roomName="currentRoom.name" :roomDescription="currentRoom.description"
          @selectReceiver="selectReceiver" style="height:100%; flex:1;" />
      </div>
      <div class="col-md-4 d-flex flex-column" style="height:100%;">
        <ListUser :usersOnline="displayUsers" @selectReceiver="selectReceiver" style="height:100%; flex:1;" />
      </div>
    </div>

    <!-- private chat -->
    <Chat v-if="selectedUser && privateRoomId" isPrivate :roomId="privateRoomId" :receiver="selectedUser"
      @closeChat="closeChat" />
  </div>
</template>

