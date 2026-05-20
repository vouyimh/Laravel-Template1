<script setup>
import { computed, inject, ref } from "vue";

const props = defineProps({
  usersOnline: {
    type: Array,
    default: [],
  },
});

defineEmits(["selectReceiver"]);

const searchQuery = ref("");
const myUser = inject("$user");

const filteredUsersList = computed(() => {
  return props.usersOnline.filter((row) =>
    row.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
})

</script>

<template>
  <div class="card mb-sm-3 mb-md-0 contacts_card h-100">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h3 class="d-flex align-items-center text-white mb-0">
          <i class="fas fa-users me-2" style="font-size: 16px; opacity: 0.9;"></i>
          Online
          <span class="badge bg-success ms-2" style="font-size: 11px;">{{ usersOnline.length }}</span>
        </h3>
      </div>
      <div class="input-group">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search users..."
          name=""
          class="form-control search"
        />
        <span class="input-group-text search_btn"
          ><i class="fas fa-search"></i
        ></span>
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
        >
          <div class="current-user-mark" v-if="user.id === myUser.id" />
          <div class="d-flex align-items-center">
            <div class="img_cont me-3">
              <img
                :src="
                  user.id === myUser.id
                    ? '/images/current_user.jpg'
                    : '/images/other_user.jpg'
                "
                class="rounded-circle user_img"
              />
              <span class="online_icon"></span>
            </div>
            <div class="user_info">
              <div class="d-flex align-items-center gap-1 flex-wrap">
                <span>{{ user.name }}{{ user.id === myUser.id ? " (You)" : "" }}</span>
                <span
                  class="badge text-bg-danger font-12px"
                  v-if="user.new_messages"
                >
                  {{ user.new_messages }}
                </span>
              </div>
              <p>{{ user.email }}</p>
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
  }

  .empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: rgba(255, 255, 255, 0.5);

    i {
      font-size: 36px;
    }

    p {
      font-size: 13px;
    }
  }
}

@media (max-width: 576px) {
  .contacts_card {
    margin-bottom: 15px !important;
  }
}
</style>
