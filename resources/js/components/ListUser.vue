<script setup>
import { computed, inject, ref } from "vue";

const props = defineProps({
  allUsers: {
    type: Array,
    default: () => [],
  },
  usersOnline: {
    type: Array,
    default: [],
  },
});

const mergedUsers = computed(() => {
  return props.allUsers.map(user => ({
    ...user,
    online: props.usersOnline.some(o => o.id === user.id)
  }));
});

defineEmits(["selectReceiver"]);

const searchQuery = ref("");
const myUser = inject("$user");

// const filteredUsersList = computed(() => {
//   return props.usersOnline.filter((row) =>
//     row.name.toLowerCase().includes(searchQuery.value.toLowerCase())
//   );
// });

const filteredUsersList = computed(() => {
  return mergedUsers.value.filter((row) =>
    row.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

</script>

<template>
  <div class="card mb-sm-3 mb-md-0 contacts_card">
    <div class="card-header">
      <h3 class="d-flex text-white">
        Online<span class="badge text-bg-success ms-2">{{
          usersOnline.length
        }}</span>
      </h3>
      <div class="input-group">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search..."
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
        <li
          v-for="user in filteredUsersList"
          :key="user.id"
          @click="$emit('selectReceiver', user)"
        >
          <div class="current-user-mark" v-if="user.id === myUser.id" />
          <div class="d-flex bd-highlight">
            <div class="img_cont">
              <img
                :src="
                  user.id === myUser.id
                    ? '/images/current_user.jpg'
                    : '/images/other_user.jpg'
                "
                class="rounded-circle user_img"
              />
              <!-- <span class="online_icon"></span> -->
               <span class="online_icon" v-if="user.online"></span>
                <span class="offline_icon" v-else></span>
            </div>
            <div class="user_info">
              <span
                >{{ user.name }}
                {{ user.id === myUser.id ? "(You)" : "" }}</span
              >
              <span
                class="badge text-bg-danger font-12px"
                v-if="user.new_messages"
              >
                {{ user.new_messages }}
              </span>
              <p>{{ user.email }}</p>
            </div>
          </div>
        </li>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.app-container {
  background: #0078d4;
  background-image: -o-linear-gradient(0deg, #0078d4, #00bcf2);
  background-image: -moz-linear-gradient(0deg, #0078d4, #00bcf2);
  background-image: -webkit-linear-gradient(0deg, #0078d4, #00bcf2);
  background-image: linear-gradient(0deg, #0078d4, #00bcf2);

  .app-header {
    position: absolute;
    width: 100%;
    top: 30px;

    .btn-logout {
      margin-right: 30px;
    }
  }
}

.chat {
  margin-top: auto;
  margin-bottom: auto;

  .contacts_body {
    padding: 0.75rem 0 !important;
    overflow-y: auto;
    white-space: nowrap;

    .contacts {
      list-style: none;
      padding: 0;

      li {
        width: 100% !important;
        padding: 5px 10px;
        transition: background-color 0.2s;
        cursor: pointer;
        position: relative;

        &:hover {
          background-color: rgba(0, 0, 0, 0.3);
        }

        &.active {
          background-color: rgba(0, 0, 0, 0.3);
        }

        .current-user-mark {
          height: 100%;
          width: 3px;
          background: #00ffa4;
          position: absolute;
          left: 0;
          top: 0;
        }

        .img_cont {
          position: relative;

          .user_img {
            height: 45px;
            width: 45px;
            border: 2px solid #f5f6fa;
          }
        }
      }
    }
  }
}

.container {
  align-content: center;
}

.user_img_msg {
  height: 40px;
  width: 40px;
  border: 2px solid #f5f6fa;
}

.online_icon {
  position: absolute;
  height: 15px;
  width: 15px;
  background-color: #4cd137;
  border-radius: 50%;
  bottom: 17px;
  right: 0;
  border: 2px solid white;
}

.offline {
  background-color: #c2c2c2 !important;
}

.user_info {
  margin-top: auto;
  margin-bottom: auto;
  margin-left: 15px;
}

.user_info span {
  font-size: 20px;
  color: white;
}

.user_info p {
  font-size: 10px;
  color: rgba(255, 255, 255, 0.6);
}

.msg_container {
  margin-top: auto;
  margin-bottom: auto;
  margin-left: 10px;
  border-radius: 25px;
  background-color: #00a0e5;
  padding: 10px;
  position: relative;
  color: white;
  word-break: break-word;
  max-width: 70%;
}

.msg_container_send {
  margin-top: auto;
  margin-bottom: auto;
  margin-right: 10px;
  border-radius: 25px;
  background-color: #42e274;
  padding: 10px;
  position: relative;
  color: white;
  word-break: break-word;
  max-width: 70%;
}

@media (max-width: 768px) {
  .app-container {
    height: auto !important;

    .app-header {
      position: initial;
      padding-top: 30px;

      .btn-logout {
        margin-right: 0;
      }
    }

    .chat {
      margin-top: 1rem;

      &:last-child,
      &:first-child {
        margin-top: 1rem;
      }
    }
  }
}

@media (max-width: 576px) {
  .contacts_card {
    margin-bottom: 15px !important;
  }
}

.font-12px {
  font-size: 12px !important;
}

.img_cont_msg {
  width: 40px;
  height: 40px;

  span {
    width: 36px;
    height: 36px;
  }
}

@keyframes wave {
  0%,
  60%,
  100% {
    transform: initial;
  }

  30% {
    transform: translateY(-15px);
  }
}

#wave {
  .dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    margin-right: 1px;
    background: white;
    animation: wave 1.3s linear infinite;
    margin-bottom: 3px;

    &:nth-child(2) {
      animation-delay: -1.1s;
    }

    &:nth-child(3) {
      animation-delay: -0.9s;
    }
  }
}

.blink-anim {
  animation: blink 2s infinite;
}

@keyframes wave {
  0%,
  60%,
  100% {
    transform: initial;
  }

  30% {
    transform: translateY(-7px);
  }
}

@keyframes blink {
  0%,
  100% {
    background: white;
  }

  50% {
    background: #2e7fd7;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to

/* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}

.slide {
  &-left,
  &-right {
    &-enter,
    &-leave {
      &-active {
        transition: all 0.5s cubic-bezier(0.55, 0, 0.1, 1);
      }
    }
  }
}

.slide {
  &-left-enter,
  &-right-leave-active {
    opacity: 0;
    transform: translate(30px, 0);
  }
}

.slide {
  &-left-leave-active,
  &-right-enter {
    opacity: 0;
    transform: translate(-30px, 0);
  }
}
</style>
