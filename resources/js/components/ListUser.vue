<script setup>
import { computed, inject, ref } from "vue";

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  openingChatForId: {
    default: null,
  },
});

defineEmits(["selectReceiver"]);

const searchQuery = ref("");
const myUser = inject("$user");

const filteredUsersList = computed(() => {
  return props.users.filter((row) =>
    row.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const onlineCount = computed(() => props.users.filter(u => u.isOnline).length);
</script>

<template>
  <div class="card mb-sm-3 mb-md-0 contacts_card h-100">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h3 class="d-flex align-items-center text-white mb-0">
          <i class="fas fa-users me-2" style="font-size: 16px; opacity: 0.9;"></i>
          Users
          <span class="badge bg-success ms-2" style="font-size: 11px;" title="Online">{{ onlineCount }} online</span>
        </h3>
      </div>
      <div class="input-group">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search users..."
          class="form-control search"
        />
        <span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
      </div>
    </div>
    <div class="card-body contacts_body">
      <div class="contacts">
        <div v-if="filteredUsersList.length === 0" class="empty-state">
          <i class="fas fa-user-slash"></i>
          <p class="mb-0 mt-2">No users found</p>
        </div>
        <li
          v-for="user in filteredUsersList"
          :key="user.id"
          @click="$emit('selectReceiver', user)"
          :class="{ 'offline-user': !user.isOnline, 'opening': openingChatForId === user.id }"
        >
          <div class="d-flex align-items-center w-100">
            <div class="img_cont me-3">
              <img
                :src="user.id === myUser.id ? '/images/current_user.jpg' : '/images/other_user.jpg'"
                class="rounded-circle user_img"
              />
              <span class="status_icon" :class="user.isOnline ? 'online_icon' : 'offline_icon'"></span>
            </div>
            <div class="user_info flex-1">
              <div class="d-flex align-items-center gap-1 flex-wrap">
                <span>{{ user.name }}{{ user.id === myUser.id ? " (You)" : "" }}</span>
                <span class="badge text-bg-danger font-12px" v-if="user.new_messages">
                  {{ user.new_messages }}
                </span>
              </div>
              <p class="mb-0">
                <span v-if="openingChatForId === user.id" class="status-text text-online">
                  Opening...
                </span>
                <span v-else class="status-text" :class="user.isOnline ? 'text-online' : 'text-offline'">
                  {{ user.isOnline ? 'Online' : 'Offline' }}
                </span>
              </p>
            </div>
          </div>
        </li>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.contacts_card {
  background: linear-gradient(180deg, #3d3d62 0%, #25253d 100%) !important;

  .contacts_body {
    background: transparent !important;
    padding: 0.75rem 0 !important;
    overflow-y: auto;
  }

  .contacts {
    list-style: none;
    padding: 0;

    li {
      width: 100%;
      padding: 0.75rem;
      cursor: pointer;
      user-select: none;
      display: flex;
      align-items: center;
      border-radius: 8px;
      margin: 0.25rem 0.5rem;
      width: calc(100% - 1rem);
      transition: background-color 0.15s ease, transform 0.15s ease;

      &:hover {
        background-color: rgba(255, 255, 255, 0.15);
        transform: translateX(4px);
      }

      &:active {
        background-color: rgba(255, 255, 255, 0.25);
        transform: translateX(2px);
      }
    }
  }

  .img_cont {
    position: relative;
    flex-shrink: 0;

    .user_img {
      height: 40px;
      width: 40px;
      object-fit: cover;
      border: 2px solid rgba(255, 255, 255, 0.6);
    }

    .status_icon {
      position: absolute;
      height: 12px;
      width: 12px;
      border-radius: 50%;
      bottom: 0;
      right: 0;
      border: 2px solid #3d3d62;
    }

    .online_icon {
      background-color: #4cd137;
      box-shadow: 0 0 0 2px rgba(76, 209, 55, 0.3);
      animation: pulse-online 2s infinite;
    }

    .offline_icon {
      background-color: #888;
    }
  }

  .user_info {
    min-width: 0;

    span {
      font-size: 14px;
      color: white;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .status-text {
      font-size: 11px;
    }

    .text-online {
      color: #4cd137;
    }

    .text-offline {
      color: rgba(255, 255, 255, 0.4);
    }
  }

  .offline-user {
    opacity: 0.65;
  }

  .opening {
    background-color: rgba(255, 255, 255, 0.2) !important;
    pointer-events: none;
  }

  .empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: rgba(255, 255, 255, 0.5);

    i { font-size: 36px; }
    p { font-size: 13px; }
  }
}

@keyframes pulse-online {
  0%, 100% { box-shadow: 0 0 0 2px rgba(76, 209, 55, 0.3); }
  50%       { box-shadow: 0 0 0 4px rgba(76, 209, 55, 0.1); }
}
</style>
