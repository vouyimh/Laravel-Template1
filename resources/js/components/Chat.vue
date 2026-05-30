<script setup>
import MessageItem from "./MessageItem.vue";
import Emoji from "./Emoji.vue";
import {
  ref,
  onMounted,
  onBeforeUnmount,
  onBeforeMount,
  inject,
  nextTick,
  computed,
  watch,
} from "vue";
import { Tooltip } from "bootstrap";
import ColorPickerModal from "./ColorPickerModal.vue";
import { throttle } from "lodash";
import axios, { AxiosError } from "axios";

const props = defineProps({
  isPrivate: {
    type: Boolean,
    default: false,
  },
  roomId: {
    type: Number,
    required: true,
  },
  roomName: String, // for shared room
  roomDescription: String, // for shared room
  receiver: Object, // for private room
});

defineEmits(["closeChat", "selectReceiver"]);

const inputMessage = ref("");
const messages = ref([]);
const isLoadingMessages = ref(false);
const rootEl = ref(null);
const messageContainer = ref(null);
const privateHasNewMessage = ref(false); // for private chat only
const privateInputEl = ref(null);
const isTyping = ref(false); // for private chat only
const isSeen = ref(false); // for private chat only
const seenAt = ref(""); // for private chat only
const msgColor = ref("#42e274"); // for private chat only
const isShowColorPicker = ref(false); // for private chat only
const isChatExpanded = ref(true); // for private chat only
const isBeingFocused = ref(false); // for private chat only
const emojiCoordinates = ref({
  x: 0,
  y: 0,
});
const isShowEmoji = ref(false);
const selectedMessage = ref(null);
const user = inject("$user");
const showToast = inject("$showToast");
const isSavingMessage = ref(false);
const fileInput = ref(null);
const imageInput = ref(null);
const videoInput = ref(null);
const isRecording = ref(false);
const mediaRecorder = ref(null);

// metadata for pagination, not related to rendering so we just make it primitive JS vars
let currentPage = 0;
let lastPage = 0;

const seenAtFormatted = computed(() => {
  var d = new Date(seenAt.value);
  return d.toLocaleString();
});

onBeforeMount(() => {
  const msgColorStorage = localStorage.getItem("msgColor");
  if (msgColorStorage) {
    msgColor.value = msgColorStorage;
  }

  initChat();
});

onMounted(() => {
  if (props.isPrivate) {
    focusPrivateInput();
  }

  $(messageContainer.value).on("scroll", async () => {
    const scroll = $(messageContainer.value).scrollTop();
    if (scroll < 1 && currentPage < lastPage) {
      getMessages(props.roomId, currentPage + 1, true);
    }
  });

  const tooltipTriggerList = rootEl.value.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new Tooltip(tooltipTriggerEl)
  );
});

onBeforeUnmount(() => {
  $(messageContainer.value).off("scroll");
  Echo.leave(`room.${props.roomId}`);
});

watch(
  () => props.roomId,
  (newVal, oldVal) => {
    Echo.leave(`room.${oldVal}`); // leave previous channel

    initChat();
  }
);

function initChat() {
  inputMessage.value = "";
  messages.value = [];
  privateHasNewMessage.value = false;
  isTyping.value = false;
  isSeen.value = false;

  getMessages(props.roomId);

  if (props.isPrivate) {
    Echo
      .private(`room.${props.roomId}`)
      .listenForWhisper("typing", (e) => {
        isTyping.value = e.isTyping;
        scrollToBottom(messageContainer.value, true);
      })
      .listenForWhisper("seen", (e) => {
        if (isSeen.value === false) {
          // check if user waiting for his message to be seen
          isSeen.value = true;
          seenAt.value = e.time;
          scrollToBottom(messageContainer.value, true);
        }
      })
      .listen("MessagePosted", (e) => {
        messages.value.push(e.message);
        privateHasNewMessage.value = true;
        isSeen.value = false;
        scrollToBottom(messageContainer.value, true);
      })
      .listen("MessageReacted", (e) => {
        onReaction(e);
      });
  } else {
    Echo
      .join(`room.${props.roomId}`) // listen to the shared room
      .listen("MessagePosted", (e) => {
        messages.value.push(e.message);
        scrollToBottom(messageContainer.value, true);
      })
      .listen("BotNotification", (e) => {
        messages.value.push({
          content: e.message,
          room: e.room,
          id: Date.now(),
          created_at: Date.now(),
          type: "bot",
          reactions: [],
        });
        scrollToBottom(messageContainer.value, true);
      })
      .listen("MessageReacted", (e) => {
        onReaction(e);
      });
  }
}

function toggleColorPicker() {
  isShowColorPicker.value = !isShowColorPicker.value;
}

function selectColor(value) {
  localStorage.setItem("msgColor", value);
  msgColor.value = value;
  toggleColorPicker();
}

function showEmoji(message, event) {
  const rect = event.target.getBoundingClientRect();
  emojiCoordinates.value.x = rect.x;
  emojiCoordinates.value.y = rect.y;
  isShowEmoji.value = true;
  selectedMessage.value = message;
}
function hideEmoji() {
  isShowEmoji.value = false;
  selectedMessage.value = null;
}

async function selectEmoji(emoji) {
  try {
    const response = await axios.post("/reactions", {
      msg_id: selectedMessage.value.id,
      emoji_id: emoji.id,
    }, {
      headers: {
        'X-Socket-ID': Echo.socketId(),
      }
    });
    const index = selectedMessage.value.reactions.findIndex(
      (item) => item.user_id === user.id
    );
    if (index > -1) {
      const reaction = selectedMessage.value.reactions[index];
      if (emoji.id === reaction.emoji_id) {
        // deactive
        selectedMessage.value.reactions.splice(index, 1);
      } else {
        reaction.emoji_id = emoji.id;
      }
    } else {
      // user first react
      const { reaction } = response.data;
      selectedMessage.value.reactions.push({
        ...reaction,
        user: user,
      });
    }
    hideEmoji();
  } catch (error) {
    console.error(error);

    if (error instanceof AxiosError) {
      showToast("Error", error.response.data.message);
    }
  }
}

function scrollToBottom(element, animate = true) {
  if (!element) {
    return;
  }
  nextTick(() => {
    // run after Vue finishes updating the DOM
    if (animate) {
      $(element).animate(
        { scrollTop: element.scrollHeight },
        { duration: "medium", easing: "swing" }
      );
    } else {
      $(element).scrollTop(element.scrollHeight);
    }
  });
}

async function saveMessage() {
  try {
    const trimmedMessage = inputMessage.value.trim();
    if (!trimmedMessage.length || isSavingMessage.value) {
      return;
    }

    isSavingMessage.value = true;
    // clean data before save to DB
    const response = await axios.post("/messages", {
      receiver: props.receiver ? props.receiver.id : undefined,
      content: trimmedMessage,
      room_id: props.roomId,
    }, {
      headers: {
        'X-Socket-ID': Echo.socketId(),
      }
    });
    if (props.isPrivate) {
      // send message indicate that user stop typing (incase Throttle function isn't called)
      Echo.private(`room.${props.roomId}`).whisper("typing", {
        user,
        isTyping: false,
      });
    }

    messages.value.push(response.data.message);

    inputMessage.value = "";
    isSeen.value = false;
    scrollToBottom(messageContainer.value, true);
  } catch (error) {
    console.error(error);
    if (error instanceof AxiosError) {
      showToast("Error", error.response.data.message);
    }
  } finally {
    isSavingMessage.value = false;
  }
}

// File upload handler
async function uploadFile(event) {
  const file = event.target.files[0];
  if (!file) return;
  await sendFile(file);
  fileInput.value.value = '';
}

// Image upload handler
async function uploadImage(event) {
  const file = event.target.files[0];
  if (!file) return;
  await sendFile(file);
  imageInput.value.value = '';
}

// Video upload handler
async function uploadVideo(event) {
  const file = event.target.files[0];
  if (!file) return;
  await sendFile(file);
  videoInput.value.value = '';
}

// Send file to server
async function sendFile(file) {
  try {
    isSavingMessage.value = true;
    const formData = new FormData();
    formData.append('file', file);
    formData.append('room_id', props.roomId);

    const response = await axios.post('/upload-file', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-Socket-ID': Echo.socketId(),
      }
    });

    messages.value.push(response.data.message);
    scrollToBottom(messageContainer.value, true);
    showToast("Success", "File uploaded successfully");
  } catch (error) {
    console.error(error);
    if (error instanceof AxiosError) {
      showToast("Error", error.response?.data?.message || "Failed to upload file");
    }
  } finally {
    isSavingMessage.value = false;
  }
}

// Voice recording toggle
async function toggleVoiceRecording() {
  if (isRecording.value) {
    stopVoiceRecording();
  } else {
    startVoiceRecording();
  }
}

// Start voice recording
async function startVoiceRecording() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder.value = new MediaRecorder(stream);
    const chunks = [];

    mediaRecorder.value.ondataavailable = (e) => chunks.push(e.data);
    mediaRecorder.value.onstop = async () => {
      const blob = new Blob(chunks, { type: 'audio/webm' });
      const file = new File([blob], `voice-${Date.now()}.webm`, { type: 'audio/webm' });
      await sendFile(file);
    };

    mediaRecorder.value.start();
    isRecording.value = true;
  } catch (error) {
    showToast("Error", "Cannot access microphone");
    console.error(error);
  }
}

// Stop voice recording
function stopVoiceRecording() {
  if (mediaRecorder.value) {
    mediaRecorder.value.stop();
    isRecording.value = false;
    mediaRecorder.value.stream.getTracks().forEach(track => track.stop());
  }
}

// Format phone number for WhatsApp (remove non-digits except leading +)
function formatPhoneNumber(phone) {
  if (!phone) return "";
  // Remove all non-digit characters except the first + if present
  let formatted = phone.replace(/[^\d+]/g, '');
  // If it doesn't start with +, remove any leading + and add it
  if (!formatted.startsWith('+')) {
    formatted = formatted.replace(/^\+/, '');
  }
  return formatted;
}

async function getMessages(room, page = 1, loadMore = false) {
  try {
    isLoadingMessages.value = true;
    const response = await axios.get(`/messages?room_id=${room}&page=${page}`, {
      headers: {
        'X-Socket-ID': Echo.socketId(),
      }
    });
    messages.value = [...response.data.data.reverse(), ...messages.value];
    currentPage = response.data.current_page;
    lastPage = response.data.last_page;

    if (loadMore) {
      nextTick(() => {
        const el = $(messageContainer.value);
        const lastFirstMessage = el
          .children()
          .eq(response.data.data.length - 1);
        el.scrollTop(lastFirstMessage.position().top - 10);
      });
    } else {
      scrollToBottom(messageContainer.value, false);
    }
  } catch (error) {
    console.error(error);
    if (error instanceof AxiosError) {
      showToast("Error", error.response.data.message);
    }
  } finally {
    isLoadingMessages.value = false;
  }
}

function onReaction(reaction) {
  const messageIndex = messages.value.findIndex(
    (m) => m.id === reaction.msg_id
  );
  if (messageIndex > -1) {
    const message = messages.value[messageIndex];
    const index = message.reactions.findIndex(
      (item) => item.user_id === reaction.user_id
    );
    if (index > -1) {
      const r = message.reactions[index];
      if (reaction.emoji_id === r.emoji_id) {
        // deactive
        message.reactions.splice(index, 1);
      } else {
        r.emoji_id = reaction.emoji_id;
      }
    } else {
      message.reactions.push({ ...reaction, user_id: reaction.user_id });
    }
  }
}

function focusPrivateInput() {
  // incase we click close private chat, privateInputEl will be null
  if (privateInputEl.value) {
    isBeingFocused.value = true;
    privateInputEl.value.focus();
    if (privateHasNewMessage.value) {
      Echo.private(`room.${props.roomId}`).whisper("seen", {
        user: user,
        seen: true,
        time: new Date(),
      });
    }
    privateHasNewMessage.value = false; // set this to false as now user is focusing the chat
  }
}

const onInputPrivateChange = throttle(function () {
  Echo.private(`room.${props.roomId}`).whisper("typing", {
    user,
    isTyping: inputMessage.value.length > 0,
  });
}, 2000); // keep tell other that we're typing because other user may close the private chat window then re open during we're typing
</script>

<template>
  <div class="card" :class="{
    'private-message-container bg-white': isPrivate,
    expand: isChatExpanded,
  }" ref="rootEl" @click="focusPrivateInput">
    <!-- @click="EXPANDDDD" -->
    <div v-if="isPrivate" class="chat-header d-flex" :class="{
      'blink-anim': privateHasNewMessage && isBeingFocused,
      'p-2 border-bottom': true,
    }">
      <div class="img_cont">
        <img :src="receiver.id === user.id
          ? '/images/current_user.jpg'
          : '/images/other_user.jpg'
          " class="rounded-circle user_img" style="width: 40px; height: 40px" />
        <span class="online_icon" style="bottom: -3px" :class="receiver.isOnline ? 'online' : 'offline'"></span>
      </div>
      <div class="user_info">
        <span style="color: black">{{
          `${receiver.name}${receiver.id === user.id ? " (You)" : ""}`
          }}</span>
        <!-- <p style="color: black;" class="mb-0">{{ chat.selectedReceiver.name }} left 50 mins ago</p> -->
      </div>
      <div class="color-picker">
        <i data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Message Color" class="fas fa-circle"
          @click.stop="toggleColorPicker" style="cursor: pointer" :style="{ color: msgColor }"></i>
      </div>
      <!-- WhatsApp Button -->
      <a
        v-if="receiver.phone"
        :href="`https://wa.me/${formatPhoneNumber(receiver.phone)}`"
        target="_blank"
        rel="noopener noreferrer"
        class="btn btn-sm btn-success ms-2"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        data-bs-title="Open WhatsApp Chat">
        <i class="fab fa-whatsapp"></i>
      </a>
      <button class="btn-close" @click="$emit('closeChat')">
        <i class="fal fa-times"></i>
      </button>
    </div>
    <div v-else class="card-header msg_head">
      <div class="d-flex align-items-center">
        <div class="room-avatar me-3">
          <i class="fas fa-hashtag"></i>
        </div>
        <div class="flex-1">
          <div class="user_info">
            <span>{{ roomName }}</span>
          </div>
          <div class="room-desc" v-if="roomDescription">
            {{ roomDescription }}
          </div>
        </div>
      </div>
    </div>
    <div class="card-body msg_card_body" ref="messageContainer" v-if="isChatExpanded">
      <div class="loading mb-2 text-center" v-if="isLoadingMessages">
        <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
          x="0px" y="0px" width="40px" height="40px" viewBox="0 0 50 50" style="enable-background: new 0 0 50 50"
          xml:space="preserve">
          <path fill="#FF6700"
            d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z"
            transform="rotate(18.3216 25 25)">
            <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25"
              dur="0.6s" repeatCount="indefinite"></animateTransform>
          </path>
        </svg>
      </div>
      <MessageItem v-for="message in messages" :key="message.id" :isPrivate="isPrivate" :message="message"
        :msgColor="msgColor" @showEmoji="showEmoji" @selectReceiver="$emit('selectReceiver', $event)" />
      <div v-if="isPrivate">
        <div class="d-flex justify-content-end" v-if="isSeen">
          <i class="font-12px">Seen {{ seenAtFormatted }}</i>
        </div>
        <div class="d-flex justify-content-start mb-4" v-if="isTyping">
          <div class="img_cont_msg">
            <img src="/images/other_user.jpg" class="rounded-circle user_img_msg" />
          </div>
          <div class="msg_container">
            <div id="wave">
              <span class="dot"></span>
              <span class="dot"></span>
              <span class="dot"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-input" v-if="isPrivate">
      <!-- Media buttons for private chat -->
      <div class="media-buttons-private" v-if="isChatExpanded">
        <!-- File Upload -->
        <input
          type="file"
          ref="fileInput"
          @change="uploadFile"
          style="display: none;"
          accept="*/*"
        />
        <button
          @click="$refs.fileInput.click()"
          class="btn"
          title="Upload file">
          <i class="fas fa-paperclip"></i>
        </button>

        <!-- Image Upload -->
        <input
          type="file"
          ref="imageInput"
          @change="uploadImage"
          style="display: none;"
          accept="image/*"
        />
        <button
          @click="$refs.imageInput.click()"
          class="btn"
          title="Upload image">
          <i class="fas fa-image"></i>
        </button>

        <!-- Video Upload -->
        <input
          type="file"
          ref="videoInput"
          @change="uploadVideo"
          style="display: none;"
          accept="video/*"
        />
        <button
          @click="$refs.videoInput.click()"
          class="btn"
          title="Upload video">
          <i class="fas fa-video"></i>
        </button>

        <!-- Voice Record -->
        <button
          @click="toggleVoiceRecording"
          class="btn"
          :class="{ 'recording': isRecording }"
          title="Record voice">
          <i class="fas fa-microphone"></i>
        </button>
      </div>

      <!-- Text input + Send button -->
      <div class="private-input-row" v-if="isChatExpanded">
        <input v-model="inputMessage" id="private_input" type="text"
          placeholder="Type a message..." @keyup.enter="saveMessage" @input="onInputPrivateChange" ref="privateInputEl"
          maxlength="2000" />
        <button type="button" @click.stop="saveMessage" class="private-send-btn">
          <div v-if="isSavingMessage" class="spinner-border text-white" role="status" style="width:18px;height:18px;">
            <span class="sr-only">Loading...</span>
          </div>
          <i v-else class="fas fa-location-arrow"></i>
        </button>
      </div>
      <small style="text-align: right; color: #999; font-size:11px;">{{ inputMessage.length }}/2000</small>
    </div>
    <div class="card-footer" v-else>
      <div class="input-group" v-if="isChatExpanded">
        <!-- File Upload Button -->
        <input
          type="file"
          ref="fileInput"
          @change="uploadFile"
          style="display: none;"
          accept="*/*"
        />
        <button
          @click="$refs.fileInput.click()"
          class="btn"
          title="Upload file"
          type="button">
          <i class="fas fa-paperclip"></i>
        </button>

        <!-- Image Upload Button -->
        <input
          type="file"
          ref="imageInput"
          @change="uploadImage"
          style="display: none;"
          accept="image/*"
        />
        <button
          @click="$refs.imageInput.click()"
          class="btn"
          title="Upload image"
          type="button">
          <i class="fas fa-image"></i>
        </button>

        <!-- Video Upload Button -->
        <input
          type="file"
          ref="videoInput"
          @change="uploadVideo"
          style="display: none;"
          accept="video/*"
        />
        <button
          @click="$refs.videoInput.click()"
          class="btn"
          title="Upload video"
          type="button">
          <i class="fas fa-video"></i>
        </button>

        <!-- Voice Record Button -->
        <button
          @click="toggleVoiceRecording"
          class="btn"
          :class="{ 'recording': isRecording }"
          title="Record voice"
          type="button">
          <i class="fas fa-microphone"></i>
        </button>

        <!-- Message Input -->
        <textarea v-model="inputMessage" name="" class="form-control type_msg" placeholder="Type your message..."
          @keyup.enter="saveMessage" autofocus maxlength="2000" />

        <!-- Send Button -->
        <button type="button" @click.stop="saveMessage" class="send_btn">
          <div style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
            <div class="spinner-border text-white" role="status" v-if="isSavingMessage"
              style="width: inherit; height: inherit;">
              <span class="sr-only">Loading...</span>
            </div>
            <i class="fas fa-location-arrow" v-else style="color: white; font-size: 16px;"></i>
          </div>
        </button>
      </div>
      <small class="float-end mt-1" style="color: #666;">{{ inputMessage.length }}/2000</small>
    </div>
    <Emoji :emojiCoordinates="emojiCoordinates" :isShow="isShowEmoji" :selectedMessage="selectedMessage"
      @hideEmoji="hideEmoji" @selectEmoji="selectEmoji" />

    <Transition name="fade" v-if="isPrivate">
      <ColorPickerModal v-if="isShowColorPicker" :isShow="isShowColorPicker" @hide="toggleColorPicker"
        @selectColor="selectColor" />
    </Transition>
  </div>
</template>

<style lang="scss">
#chat-app, #room-app {
  height: 100%;
  display: flex;
  flex-direction: column;

  > .flex {
    flex: 1;
    min-height: 0;
  }

  .card {
    z-index: 1;
    height: 100%;
    border-radius: 12px !important;
    background-color: #ffffff !important;
    border: 1px solid #e0e0e0 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;

    &.contacts_card {
      background: linear-gradient(180deg, #3d3d62 0%, #25253d 100%) !important;
      border: none !important;
    }

    &.bg-white {
      background-color: #ffffff !important;
    }

    .card-header {
      border-radius: 12px 12px 0 0 !important;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none !important;
      padding: 1.5rem !important;
      color: white;

      .search_btn {
        border-radius: 0 8px 8px 0 !important;
        background-color: rgba(255, 255, 255, 0.2) !important;
        border: 0 !important;
        color: white !important;
        cursor: pointer;
        transition: all 0.2s ease;

        &:hover {
          background-color: rgba(255, 255, 255, 0.3) !important;
        }
      }

      .search {
        border-radius: 8px 0 0 8px !important;
        background-color: rgba(255, 255, 255, 0.2) !important;
        border: 0 !important;
        color: white !important;

        &:focus {
          box-shadow: none !important;
          outline: 0px !important;
          background-color: rgba(255, 255, 255, 0.3) !important;
        }

        &::placeholder {
          color: rgba(255, 255, 255, 0.7);
        }
      }
    }

    .msg_head {
      position: relative;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none !important;
      padding: 1rem 1.5rem !important;
      color: white;

      .room-avatar {
        width: 42px;
        height: 42px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        i {
          font-size: 18px;
          color: white;
        }
      }

      .user_info {
        margin: 0;

        span {
          font-size: 17px;
          font-weight: 700;
          color: #ffffff;
          text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
          letter-spacing: 0.2px;
        }
      }

      .room-desc {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 2px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
      }

      .flex-1 {
        flex: 1;
        min-width: 0;
      }
    }

    .msg_card_body {
      overflow-y: auto;
      flex: 1;
      background-color: #f8f9fa;
      padding: 1.5rem;
      color: #333;

      &::-webkit-scrollbar {
        width: 6px;
      }

      &::-webkit-scrollbar-track {
        background: transparent;
      }

      &::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;

        &:hover {
          background: #999;
        }
      }

      .font-12px {
        color: #999;
        font-size: 12px !important;
      }

      #wave .dot {
        background: white;
      }
    }

    .card-footer {
      border-radius: 0 0 12px 12px !important;
      border-top: 1px solid #e0e0e0 !important;
      background-color: white;
      padding: 1rem !important;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;

      .input-group {
        display: flex;
        gap: 0;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #ddd;
        background-color: white;

        .btn {
          padding: 0.6rem 0.75rem;
          border: none;
          border-right: 1px solid #ddd;
          background-color: white;
          color: #667eea;
          cursor: pointer;
          transition: all 0.2s ease;
          font-size: 14px;
          flex-shrink: 0;

          &:last-of-type:not(.form-control) {
            border-right: none;
          }

          &:hover {
            background-color: #f8f9fa;
            color: #5568d3;
          }

          &.recording {
            background-color: #f56565;
            color: white;
            animation: pulse 1s infinite;
          }

          i {
            font-size: 16px;
          }
        }

        .type_msg {
          background-color: white !important;
          border: none !important;
          color: #333 !important;
          height: 60px !important;
          overflow-y: auto;
          padding: 0.75rem !important;
          font-size: 14px;
          resize: none;
          flex: 1;
          border-right: 1px solid #ddd;

          &:focus {
            box-shadow: none !important;
            outline: 0px !important;
            background-color: white !important;
          }

          &::placeholder {
            color: #999;
          }
        }

        .send_btn {
          border-radius: 0 !important;
          background-color: #667eea !important;
          border: 0 !important;
          color: white !important;
          cursor: pointer;
          transition: all 0.2s ease;
          padding: 0.75rem 1rem !important;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          border-left: none;

          &:hover {
            background-color: #5568d3 !important;
          }

          &:active {
            transform: scale(0.98);
          }
        }
      }
    }
  }

  .private-message-container {
    border-radius: 12px !important;
    background-color: white;
    position: relative;
    width: 100%;
    height: 100%;
    border: 1px solid #e0e0e0;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;

    .chat-header {
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      transition: all 0.2s;
      cursor: pointer;
      padding: 1rem;
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;

      &:hover {
        box-shadow: inset 0 -2px 4px rgba(0, 0, 0, 0.1);
      }

      .img_cont {
        position: relative;
        flex-shrink: 0;

        .user_img {
          border: 2px solid white !important;
        }
      }

      .user_info {
        margin-left: 12px;
        flex: 1;
        min-width: 0;

        span {
          display: block;
          font-size: 14px;
          font-weight: 600;
          color: #ffffff;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          margin-bottom: 2px;
          text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        p {
          font-size: 11px;
          color: rgba(255, 255, 255, 0.9);
          margin: 0;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
      }

      .color-picker {
        position: relative;
        margin-left: auto;
        margin-right: 12px;

        i {
          font-size: 18px;
          cursor: pointer;
          transition: transform 0.2s;

          &:hover {
            transform: scale(1.15);
          }
        }
      }

      .btn-success {
        padding: 0.4rem 0.6rem !important;
        font-size: 16px !important;
        margin: 0 8px 0 0 !important;
        transition: all 0.2s ease;

        &:hover {
          background-color: #128c7e !important;
          transform: scale(1.1);
        }
      }

      .btn-close {
        outline: none;
        border: none;
        background: none;
        color: white;
        flex-shrink: 0;

        i {
          font-size: 18px;
          transition: transform 0.2s;

          &:hover {
            transform: scale(1.2);
          }
        }
      }
    }

    .msg_card_body {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
      background-color: #f8f9fa;

      &::-webkit-scrollbar {
        width: 5px;
      }

      &::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
      }
    }

    .text-input {
      padding: 0.75rem;
      border-top: 1px solid #e0e0e0;
      background-color: white;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;

      .private-input-row {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;

        input {
          flex: 1;
          height: 40px;
          border: none;
          outline: none;
          padding: 0.5rem 0.75rem;
          font-size: 13px;
          background-color: white;
          color: #333;

          &:focus {
            box-shadow: none;
          }

          &::placeholder {
            color: #999;
          }
        }

        .private-send-btn {
          height: 40px;
          width: 44px;
          background-color: #667eea;
          border: none;
          outline: none;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          flex-shrink: 0;
          transition: background-color 0.2s;
          -webkit-tap-highlight-color: transparent;

          &:hover {
            background-color: #5568d3;
          }

          &:active {
            background-color: #4456c7;
            transform: scale(0.96);
          }

          i {
            color: white;
            font-size: 15px;
          }
        }
      }

      small {
        color: #999;
        font-size: 11px;
        text-align: right;
      }
    }

    .media-buttons-private {
      display: flex;
      gap: 0.4rem;
      flex-wrap: wrap;
      background-color: #f8f9fa;
      padding: 0.5rem;
      border-radius: 6px;
      border: 1px solid #e0e0e0;

      .btn {
        padding: 0.4rem 0.6rem;
        border: 1px solid #ddd;
        background-color: white;
        color: #667eea;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 12px;
        border-radius: 4px;
        flex-shrink: 0;

        &:hover {
          background-color: #f0f0f0;
          border-color: #667eea;
          color: #5568d3;
          transform: scale(1.05);
        }

        &.recording {
          background-color: #f56565;
          color: white;
          border-color: #f56565;
          animation: pulse 1s infinite;
        }

        i {
          font-size: 13px;
        }
      }
    }
  }
}
</style>


<style lang="scss">
.app-container {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;

  .app-header {
    position: absolute;
    width: 100%;
    top: 30px;
    z-index: 10;

    .btn-logout {
      margin-right: 30px;
      padding: 0.5rem 1rem;
      background-color: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      transition: all 0.2s ease;

      &:hover {
        background-color: rgba(255, 255, 255, 0.3);
      }
    }
  }
}

.chat {
  margin-top: auto;
  margin-bottom: auto;
  min-height: 600px;

  .contacts_body {
    padding: 0.75rem 0 !important;
    overflow-y: auto;
    white-space: nowrap;

    &::-webkit-scrollbar {
      width: 6px;
    }

    &::-webkit-scrollbar-track {
      background: transparent;
    }

    &::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.3);
      border-radius: 3px;

      &:hover {
        background: rgba(255, 255, 255, 0.5);
      }
    }

    .contacts {
      list-style: none;
      padding: 0;

      li {
        width: 100% !important;
        padding: 0.75rem;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        display: flex;
        align-items: center;
        border-radius: 8px;
        margin: 0.25rem 0.5rem;

        &:hover {
          background-color: rgba(255, 255, 255, 0.15);
          transform: translateX(4px);
        }

        &.active {
          background-color: rgba(255, 255, 255, 0.25);
          box-shadow: inset 3px 0 0 #00ffa4;
        }

        .current-user-mark {
          height: 100%;
          width: 3px;
          background: #00ffa4;
          position: absolute;
          left: 0;
          top: 0;
          display: none;
        }

        .img_cont {
          position: relative;
          flex-shrink: 0;

          .user_img {
            height: 45px;
            width: 45px;
            border: 2px solid white;
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
  height: 12px;
  width: 12px;
  background-color: #4cd137;
  border-radius: 50%;
  bottom: 0;
  right: 0;
  border: 3px solid white;
  box-shadow: 0 0 0 2px #667eea;
  animation: pulse-online 2s infinite;
}

.offline {
  background-color: #e0e0e0 !important;
  animation: none !important;
}

@keyframes pulse-online {
  0%, 100% {
    box-shadow: 0 0 0 2px #667eea, 0 0 0 6px rgba(76, 209, 55, 0.3);
  }
  50% {
    box-shadow: 0 0 0 2px #667eea, 0 0 0 8px rgba(76, 209, 55, 0.1);
  }
}


.msg_container {
  margin-top: auto;
  margin-bottom: auto;
  margin-left: 10px;
  border-radius: 18px 18px 18px 4px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 12px 16px;
  position: relative;
  color: white;
  word-break: break-word;
  max-width: 70%;
  box-shadow: 0 2px 6px rgba(102, 126, 234, 0.2);
  font-size: 14px;
  line-height: 1.4;
}

.msg_container_send {
  margin-top: auto;
  margin-bottom: auto;
  margin-right: 10px;
  border-radius: 18px 18px 4px 18px;
  background: linear-gradient(135deg, #42e274 0%, #38c76b 100%);
  padding: 12px 16px;
  position: relative;
  color: white;
  word-break: break-word;
  max-width: 70%;
  box-shadow: 0 2px 6px rgba(66, 226, 116, 0.2);
  font-size: 14px;
  line-height: 1.4;
}

@media (max-width: 768px) {
  #chat-app, #room-app {
    .card {
      height: 100%;
      min-height: 400px;
    }

    .private-message-container {
      width: 100%;
      height: 100%;
    }
  }

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
      min-height: 400px;

      &:last-child,
      &:first-child {
        margin-top: 1rem;
      }
    }
  }
}

@media (max-width: 576px) {
  #chat-app, #room-app {
    .card {
      height: 100%;
      min-height: 300px;
    }

    .private-message-container {
      width: 100%;
    }
  }

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

/* Media buttons styling for group chat */
.input-group {
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #ddd;
  background-color: white;
  display: flex;
  gap: 0;

  .btn {
    padding: 0.6rem 0.75rem;
    border: none;
    border-right: 1px solid #ddd;
    background-color: white;
    color: #667eea;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;

    &:last-of-type:not(.form-control) {
      border-right: none;
    }

    &:hover {
      background-color: #f8f9fa;
      color: #5568d3;
    }

    &.recording {
      background-color: #f56565;
      color: white;
      animation: pulse 1s infinite;
    }

    i {
      font-size: 16px;
    }
  }

  .form-control {
    border: none !important;
    padding: 0.75rem;
    font-size: 14px;

    &:focus {
      box-shadow: none !important;
      outline: none !important;
    }

    &::placeholder {
      color: #999;
    }
  }
}

/* WhatsApp button styling */
.btn-success {
  background-color: #25D366 !important;
  border-color: #25D366 !important;

  &:hover {
    background-color: #20ba58 !important;
    border-color: #20ba58 !important;
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(0.95);
  }
}

/* Loading spinner animation */
.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem;

  svg {
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
  }
}
</style>
