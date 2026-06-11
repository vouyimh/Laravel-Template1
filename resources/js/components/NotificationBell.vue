<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import axios from "axios";

const notifications  = ref([]);
const unreadCount    = ref(0);
const isOpen         = ref(false);
const isLoading      = ref(false);

// ── Fetch ──────────────────────────────────────────────────────────────────

async function fetchNotifications() {
  try {
    isLoading.value = true;
    const res        = await axios.get("/notifications");
    notifications.value = res.data;
    unreadCount.value   = res.data.filter((n) => !n.read_at).length;
  } catch (e) {
    console.error("Failed to load notifications", e);
  } finally {
    isLoading.value = false;
  }
}

async function markAsRead(notification) {
  if (notification.read_at) return;
  try {
    await axios.patch(`/notifications/${notification.id}/read`);
    notification.read_at = new Date().toISOString();
    unreadCount.value    = Math.max(0, unreadCount.value - 1);
  } catch (e) {
    console.error(e);
  }
}

async function markAllAsRead() {
  try {
    await axios.patch("/notifications/mark-all-read");
    notifications.value.forEach((n) => (n.read_at = new Date().toISOString()));
    unreadCount.value = 0;
  } catch (e) {
    console.error(e);
  }
}

// ── Toggle ─────────────────────────────────────────────────────────────────

function toggle() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) fetchNotifications();
}

function closeOnOutside(e) {
  if (!e.target.closest("#notification-bell")) {
    isOpen.value = false;
  }
}

// ── Real-time via Echo ─────────────────────────────────────────────────────

let echoChannel = null;

function initEcho() {
  if (typeof Echo === "undefined" || !window.__app__?.user?.id) return;

  echoChannel = Echo.private(`App.Models.User.${window.__app__.user.id}`).notification(
    (notification) => {
      // Prepend new notification and bump badge
      notifications.value.unshift({
        id:         notification.id ?? Date.now().toString(),
        type:       notification.type ?? "",
        data:       notification,
        read_at:    null,
        created_at: "just now",
      });
      unreadCount.value++;
    }
  );
}

// ── Icon helpers ───────────────────────────────────────────────────────────

function iconFor(type) {
  const icons = {
    task_assigned:        "bx bx-task text-primary",
    task_status_changed:  "bx bx-refresh text-warning",
    new_private_message:  "bx bx-message-dots text-success",
  };
  return icons[type] ?? "bx bx-bell text-secondary";
}

// ── Lifecycle ──────────────────────────────────────────────────────────────

onMounted(() => {
  fetchNotifications();
  initEcho();
  document.addEventListener("click", closeOnOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", closeOnOutside);
  if (echoChannel) Echo.leave(`private-App.Models.User.${window.__app__?.user?.id}`);
});
</script>

<template>
  <div id="notification-bell" class="nav-item dropdown me-2" style="position:relative;">

    <!-- Bell button -->
    <button
      class="btn btn-icon btn-ghost-secondary rounded-circle"
      @click.stop="toggle"
      style="position:relative;"
    >
      <i class="bx bx-bell icon-md"></i>
      <span
        v-if="unreadCount > 0"
        class="badge bg-danger rounded-pill"
        style="position:absolute;top:4px;right:4px;font-size:9px;min-width:16px;height:16px;line-height:16px;padding:0 4px;"
      >{{ unreadCount > 99 ? "99+" : unreadCount }}</span>
    </button>

    <!-- Dropdown panel -->
    <div
      v-if="isOpen"
      class="dropdown-menu dropdown-menu-end show shadow-lg"
      style="width:360px;max-height:480px;overflow-y:auto;right:0;left:auto;top:100%;margin-top:8px;"
      @click.stop
    >
      <!-- Header -->
      <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
        <h6 class="mb-0 fw-semibold">
          Notifications
          <span v-if="unreadCount > 0" class="badge bg-danger ms-1" style="font-size:11px;">{{ unreadCount }}</span>
        </h6>
        <button
          v-if="unreadCount > 0"
          class="btn btn-sm btn-link text-muted p-0"
          @click="markAllAsRead"
        >Mark all read</button>
      </div>

      <!-- Loading state -->
      <div v-if="isLoading" class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
      </div>

      <!-- Empty state -->
      <div
        v-else-if="notifications.length === 0"
        class="text-center text-muted py-5"
      >
        <i class="bx bx-bell-off" style="font-size:32px;"></i>
        <p class="mb-0 mt-2 small">No notifications yet</p>
      </div>

      <!-- Notification list -->
      <template v-else>
        <a
          v-for="n in notifications"
          :key="n.id"
          :href="n.data.url ?? '#'"
          class="dropdown-item d-flex align-items-start gap-3 py-2 px-3"
          :class="{ 'bg-light': !n.read_at }"
          @click="markAsRead(n)"
          style="white-space:normal;"
        >
          <!-- Icon -->
          <div class="flex-shrink-0 mt-1">
            <span class="avatar avatar-xs rounded-circle bg-label-secondary d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
              <i :class="iconFor(n.data.type ?? n.type)" style="font-size:15px;"></i>
            </span>
          </div>

          <!-- Content -->
          <div class="flex-grow-1 overflow-hidden">
            <p class="mb-0 small fw-medium text-truncate" style="max-width:260px;">
              {{ n.data.message }}
            </p>
            <small class="text-muted">{{ n.created_at }}</small>
          </div>

          <!-- Unread dot -->
          <div v-if="!n.read_at" class="flex-shrink-0 mt-2">
            <span class="badge bg-primary rounded-circle p-1" style="width:8px;height:8px;display:inline-block;"></span>
          </div>
        </a>
      </template>
    </div>

  </div>
</template>
