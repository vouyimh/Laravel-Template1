<script setup>
import ListUser from "../components/ListUser.vue";
import { ref, onBeforeMount, onBeforeUnmount, inject, computed, watch } from "vue";
import Chat from "../components/Chat.vue";
import axios from "axios";
import { registerPushNotifications } from "../composables/usePushNotifications.js";

const selectedUser = ref(null);
const onlineIds = ref(new Set());
const filteredUsers = ref([]);
const unreadCounts = ref({});
const privateRoomId = ref(null);
const openingChatForId = ref(null); // which user is being opened right now
const user = inject("$user");
const appName = inject("$appName");
const showToast = inject("$showToast");

const fetchAllowedUsers = async () => {
  try {
    const response = await axios.get('/chat-users');
    filteredUsers.value = response.data;
  } catch (error) {
    console.error('Error fetching allowed users:', error);
  }
};

onBeforeMount(async () => {
  await fetchAllowedUsers();
  registerPushNotifications(); // ask permission and register push subscription

  Echo.join('online')
    .here((users) => {
      onlineIds.value = new Set(users.map(u => Number(u.id)));
    })
    .joining((joinedUser) => {
      onlineIds.value = new Set([...onlineIds.value, Number(joinedUser.id)]);
      if (selectedUser.value && Number(joinedUser.id) === Number(selectedUser.value.id)) {
        selectedUser.value.isOnline = true;
      }
    })
    .leaving((leftUser) => {
      const next = new Set(onlineIds.value);
      next.delete(Number(leftUser.id));
      onlineIds.value = next;
      if (selectedUser.value && Number(leftUser.id) === Number(selectedUser.value.id)) {
        selectedUser.value.isOnline = false;
      }
    });

  Echo.private(`room.${user.id}`).listen("MessagePosted", (e) => {
    const senderId = Number(e.message.user.id);
    if (!selectedUser.value || Number(selectedUser.value.id) !== senderId) {
      unreadCounts.value = {
        ...unreadCounts.value,
        [senderId]: (unreadCounts.value[senderId] || 0) + 1,
      };
    }
  });
});

onBeforeUnmount(() => {
  Echo.leave('online');
});

async function selectReceiver(receiver) {
  if (Number(receiver.id) === Number(user.id)) {
    showToast("Error", "You can't chat with yourself");
    return;
  }

  if (openingChatForId.value) return; // prevent double-click

  openingChatForId.value = receiver.id;
  try {
    const response = await axios.post(`/start_chat`, { receiver_id: receiver.id });
    privateRoomId.value = response.data.id;

    const fullUserData = filteredUsers.value.find(u => Number(u.id) === Number(receiver.id)) || receiver;
    selectedUser.value = {
      ...fullUserData,
      isOnline: onlineIds.value.has(Number(receiver.id)),
    };

    // Clear unread count for this user
    const next = { ...unreadCounts.value };
    delete next[receiver.id];
    unreadCounts.value = next;
  } catch (error) {
    console.error('selectReceiver error:', error);
    showToast("Error", error?.response?.data?.error || "Could not open chat");
  } finally {
    openingChatForId.value = null;
  }
}

function closeChat() {
  selectedUser.value = null;
  privateRoomId.value = null;
}

// All allowed users with online/offline status — online first
const displayUsers = computed(() => {
  return filteredUsers.value
    .map(u => ({
      ...u,
      isOnline: onlineIds.value.has(Number(u.id)),
      new_messages: unreadCounts.value[u.id] || 0,
    }))
    .sort((a, b) => b.isOnline - a.isOnline);
});

const totalUnreadPrivateMessages = computed(() =>
  Object.values(unreadCounts.value).reduce((sum, n) => sum + n, 0)
);

watch(totalUnreadPrivateMessages, (count) => {
  document.title = count > 0 ? `(${count}) - ${appName}` : appName;
});
</script>

<template>
  <div style="height:100%;">
    <div class="row h-100 g-3" style="height:100%;">

      <!-- Left: User list -->
      <div class="col-md-4 d-flex flex-column" style="height:100%;">
        <ListUser :users="displayUsers" :openingChatForId="openingChatForId" @selectReceiver="selectReceiver" style="height:100%; flex:1;" />
      </div>

      <!-- Right: Private chat or empty state -->
      <div class="col-md-8 d-flex flex-column" style="height:100%;">
        <Chat
          v-if="selectedUser && privateRoomId"
          isPrivate
          :roomId="privateRoomId"
          :receiver="selectedUser"
          @closeChat="closeChat"
          style="height:100%; flex:1;"
        />
        <div
          v-else
          class="card h-100 d-flex flex-column align-items-center justify-content-center text-center"
          style="background: linear-gradient(180deg, #3d3d62 0%, #25253d 100%); color: rgba(255,255,255,0.4); border:none;"
        >
          <i class="fas fa-comments mb-3" style="font-size: 52px;"></i>
          <p class="mb-1" style="font-size:16px; font-weight:500; color:rgba(255,255,255,0.6);">Select a user to start chatting</p>
          <p style="font-size:13px;">Choose someone from the list on the left</p>
        </div>
      </div>

    </div>
  </div>
</template>
