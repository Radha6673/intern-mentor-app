<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    conversations: {
        type: Array,
        default: () => []
    },
    contacts: {
        type: Array,
        default: () => []
    },
    activeConversation: {
        type: Object,
        default: null
    },
    messages: {
        type: Array,
        default: () => []
    }
});

const activeMessages = ref([...props.messages]);
const search = ref('');
const activeTab = ref('chats'); // 'chats' or 'contacts'
const messagesContainer = ref(null);
const fileInput = ref(null);
const previewUrl = ref(null);
const previewType = ref(null);
const selectedFile = ref(null);
const lightboxMedia = ref(null); // For image/video lightbox popup
const isPolishingMessage = ref(false);
let currentChannel = null;

const form = useForm({
    message: '',
    file: null,
});

// Watch props.messages update
watch(() => props.messages, (newMsgs) => {
    activeMessages.value = [...newMsgs];
    scrollToBottom();
}, { deep: true });

// Listen on Laravel Echo WebSocket private channel
const subscribeToConversation = (conversationId) => {
    leaveConversationChannel();

    if (!conversationId || !window.Echo) return;

    currentChannel = window.Echo.private(`conversation.${conversationId}`);
    currentChannel.listen('.MessageSent', (e) => {
        if (e.message) {
            const exists = activeMessages.value.some(m => m.id === e.message.id);
            if (!exists) {
                activeMessages.value.push(e.message);
                scrollToBottom();
            }
        }
    });
};

const leaveConversationChannel = () => {
    if (props.activeConversation && window.Echo) {
        window.Echo.leave(`conversation.${props.activeConversation.id}`);
    }
    currentChannel = null;
};

watch(() => props.activeConversation?.id, (newConvId, oldConvId) => {
    if (oldConvId && window.Echo) {
        window.Echo.leave(`conversation.${oldConvId}`);
    }
    if (newConvId) {
        subscribeToConversation(newConvId);
    }
});

// Filtered conversations
const filteredConversations = computed(() => {
    if (!search.value.trim()) return props.conversations;
    const q = search.value.toLowerCase();
    return props.conversations.filter(c => 
        c.other_user?.name?.toLowerCase().includes(q) ||
        c.other_user?.email?.toLowerCase().includes(q)
    );
});

// Filtered contacts
const filteredContacts = computed(() => {
    if (!search.value.trim()) return props.contacts;
    const q = search.value.toLowerCase();
    return props.contacts.filter(c => 
        c.name?.toLowerCase().includes(q) ||
        c.email?.toLowerCase().includes(q)
    );
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    selectedFile.value = file;
    form.file = file;

    if (file.type.startsWith('image/')) {
        previewType.value = 'image';
        previewUrl.value = URL.createObjectURL(file);
    } else if (file.type.startsWith('video/')) {
        previewType.value = 'video';
        previewUrl.value = URL.createObjectURL(file);
    } else {
        previewType.value = 'file';
        previewUrl.value = null;
    }
};

const clearSelectedFile = () => {
    selectedFile.value = null;
    form.file = null;
    previewUrl.value = null;
    previewType.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const triggerFileInput = () => {
    if (fileInput.value) fileInput.value.click();
};

const polishChatMessage = async () => {
    if (!form.message.trim() || isPolishingMessage.value) return;
    isPolishingMessage.value = true;
    try {
        const response = await axios.post(route('ai.polish'), {
            text: form.message,
            context: 'chat'
        });
        if (response.data && response.data.polished_text) {
            form.message = response.data.polished_text;
        }
    } catch (error) {
        console.error('Error polishing chat message:', error);
    } finally {
        isPolishingMessage.value = false;
    }
};

const sendMessage = () => {
    if (!form.message.trim() && !form.file) return;
    if (!props.activeConversation) return;

    form.post(route('chat.send', { conversation: props.activeConversation.id }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('message', 'file');
            clearSelectedFile();
        }
    });
};

const openLightbox = (media) => {
    lightboxMedia.value = media;
};

const closeLightbox = () => {
    lightboxMedia.value = null;
};

const formatTime = (datetimeStr) => {
    if (!datetimeStr) return '';
    const date = new Date(datetimeStr);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const formatDateDivider = (datetimeStr) => {
    if (!datetimeStr) return '';
    const date = new Date(datetimeStr);
    return date.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
};

onMounted(() => {
    scrollToBottom();
    if (props.activeConversation) {
        subscribeToConversation(props.activeConversation.id);
    }
});

onUnmounted(() => {
    leaveConversationChannel();
});
</script>

<template>
    <Head title="Chat & Media Sharing" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-gray-800">
                    💬 Intern & Mentor Chat
                </h2>
                <span class="text-xs font-semibold px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full">
                    Real-time Media Hub
                </span>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-white shadow-xl border border-gray-100 flex h-[calc(100vh-210px)] min-h-[550px]">
                    
                    <!-- Left Sidebar (Conversations & Contacts) -->
                    <div class="w-80 md:w-96 border-r border-gray-200 bg-slate-50 flex flex-col shrink-0">
                        <!-- Search & Tabs -->
                        <div class="p-4 border-b border-gray-200 bg-white">
                            <div class="relative mb-3">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    🔍
                                </span>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search chat or user..."
                                    class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-gray-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                />
                            </div>

                            <div class="flex rounded-lg bg-gray-100 p-1 text-xs font-medium">
                                <button
                                    @click="activeTab = 'chats'"
                                    :class="[
                                        'flex-1 py-1.5 rounded-md text-center transition font-semibold',
                                        activeTab === 'chats' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-800'
                                    ]"
                                >
                                    Chats ({{ conversations.length }})
                                </button>
                                <button
                                    @click="activeTab = 'contacts'"
                                    :class="[
                                        'flex-1 py-1.5 rounded-md text-center transition font-semibold',
                                        activeTab === 'contacts' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-800'
                                    ]"
                                >
                                    Contacts ({{ contacts.length }})
                                </button>
                            </div>
                        </div>

                        <!-- Conversation List -->
                        <div v-if="activeTab === 'chats'" class="flex-1 overflow-y-auto divide-y divide-gray-100">
                            <div v-if="filteredConversations.length === 0" class="p-8 text-center text-gray-400 text-sm">
                                No active conversations found. Switch to Contacts tab to start a chat!
                            </div>
                            <Link
                                v-for="conv in filteredConversations"
                                :key="conv.id"
                                :href="route('chat.index', { conversation: conv.id })"
                                :class="[
                                    'flex items-center gap-3 p-3.5 hover:bg-indigo-50/50 transition cursor-pointer',
                                    activeConversation && activeConversation.id === conv.id ? 'bg-indigo-50 border-l-4 border-indigo-600' : ''
                                ]"
                            >
                                <div class="relative shrink-0">
                                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-base shadow">
                                        {{ conv.other_user?.name?.charAt(0).toUpperCase() }}
                                    </div>
                                    <span v-if="conv.unread_count > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white">
                                        {{ conv.unread_count }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-baseline mb-0.5">
                                        <h4 class="text-sm font-semibold text-gray-900 truncate">
                                            {{ conv.other_user?.name }}
                                        </h4>
                                        <span v-if="conv.latest_message" class="text-[11px] text-gray-400 shrink-0">
                                            {{ formatTime(conv.latest_message.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate flex items-center gap-1">
                                        <span v-if="conv.latest_message">
                                            <span v-if="conv.latest_message.type === 'image'">📷 Photo</span>
                                            <span v-else-if="conv.latest_message.type === 'video'">🎥 Video</span>
                                            <span v-else-if="conv.latest_message.type === 'file'">📎 Document</span>
                                            <span v-else>{{ conv.latest_message.message }}</span>
                                        </span>
                                        <span v-else class="italic text-gray-400">Click to chat</span>
                                    </p>
                                </div>
                            </Link>
                        </div>

                        <!-- Contacts List -->
                        <div v-else class="flex-1 overflow-y-auto divide-y divide-gray-100">
                            <div v-if="filteredContacts.length === 0" class="p-8 text-center text-gray-400 text-sm">
                                No contacts available.
                            </div>
                            <div
                                v-for="contact in filteredContacts"
                                :key="contact.id"
                                class="flex items-center justify-between p-3.5 hover:bg-slate-100 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-700 text-white flex items-center justify-center font-bold text-sm">
                                        {{ contact.name?.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900">{{ contact.name }}</h4>
                                        <span class="text-xs px-2 py-0.5 rounded-full capitalize font-medium"
                                              :class="contact.role === 'mentor' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
                                            {{ contact.role }}
                                        </span>
                                    </div>
                                </div>
                                <Link
                                    :href="route('chat.start', { user: contact.id })"
                                    method="post"
                                    as="button"
                                    class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition flex items-center gap-1 cursor-pointer"
                                >
                                    💬 Message
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Right Main Chat Window -->
                    <div class="flex-1 flex flex-col bg-white">
                        <template v-if="activeConversation">
                            <!-- Active Chat Header -->
                            <div class="p-4 border-b border-gray-200 bg-white flex items-center justify-between shadow-sm z-10">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center font-bold text-base shadow">
                                        {{ activeConversation.other_user?.name?.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                            {{ activeConversation.other_user?.name }}
                                            <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                                :class="activeConversation.other_user?.role === 'mentor' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
                                                {{ activeConversation.other_user?.role }}
                                            </span>
                                        </h3>
                                        <p class="text-xs text-gray-500">{{ activeConversation.other_user?.email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="fetchLatestMessages" title="Refresh Messages" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-lg transition">
                                        🔄
                                    </button>
                                </div>
                            </div>

                            <!-- Messages Scroll Stream -->
                            <div ref="messagesContainer" class="flex-1 p-4 overflow-y-auto space-y-4 bg-slate-50/60">
                                <div v-if="activeMessages.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                                    <div class="text-4xl mb-2">👋</div>
                                    <p class="text-sm font-medium">Say Hi! Start conversation with {{ activeConversation.other_user?.name }}</p>
                                </div>

                                <template v-for="(msg, index) in activeMessages" :key="msg.id">
                                    <!-- Message Bubble -->
                                    <div :class="['flex flex-col', msg.sender_id === $page.props.auth.user.id ? 'items-end' : 'items-start']">
                                        <div class="text-[11px] text-gray-400 mb-1 px-1 flex items-center gap-1">
                                            <span class="font-medium text-gray-600">{{ msg.sender?.name }}</span>
                                            <span>•</span>
                                            <span>{{ formatTime(msg.created_at) }}</span>
                                        </div>

                                        <div
                                            :class="[
                                                'max-w-[75%] sm:max-w-[65%] rounded-2xl p-3.5 shadow-sm text-sm space-y-2',
                                                msg.sender_id === $page.props.auth.user.id
                                                    ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-tr-none'
                                                    : 'bg-white border border-gray-200 text-gray-900 rounded-tl-none'
                                            ]"
                                        >
                                            <!-- Text Content -->
                                            <p v-if="msg.message" class="whitespace-pre-wrap leading-relaxed">{{ msg.message }}</p>

                                            <!-- Image Media -->
                                            <div v-if="msg.type === 'image' && msg.file_url" class="mt-1">
                                                <img
                                                    :src="msg.file_url"
                                                    :alt="msg.file_name || 'Uploaded Image'"
                                                    class="max-h-64 rounded-xl object-cover border border-black/10 cursor-pointer hover:opacity-90 transition shadow-sm"
                                                    @click="openLightbox({ type: 'image', url: msg.file_url, name: msg.file_name })"
                                                />
                                            </div>

                                            <!-- Video Media -->
                                            <div v-if="msg.type === 'video' && msg.file_url" class="mt-1">
                                                <video
                                                    controls
                                                    preload="metadata"
                                                    class="max-h-72 w-full rounded-xl border border-black/10 shadow-sm"
                                                >
                                                    <source :src="msg.file_url" />
                                                    Your browser does not support video play.
                                                </video>
                                            </div>

                                            <!-- Document File Media -->
                                            <div v-if="msg.type === 'file' && msg.file_url" class="mt-1">
                                                <a
                                                    :href="msg.file_url"
                                                    target="_blank"
                                                    download
                                                    :class="[
                                                        'flex items-center gap-2 p-2.5 rounded-xl border transition text-xs font-semibold',
                                                        msg.sender_id === $page.props.auth.user.id
                                                            ? 'bg-indigo-800/50 border-indigo-500 text-white hover:bg-indigo-800'
                                                            : 'bg-slate-100 border-slate-200 text-indigo-700 hover:bg-slate-200'
                                                    ]"
                                                >
                                                    <span class="text-base">📄</span>
                                                    <span class="truncate flex-1">{{ msg.file_name || 'Download File' }}</span>
                                                    <span class="text-xs">⬇️</span>
                                                </a>
                                            </div>

                                            <!-- Read Status Indicator for Sender -->
                                            <div v-if="msg.sender_id === $page.props.auth.user.id" class="text-[10px] text-right opacity-80 pt-0.5">
                                                <span v-if="msg.is_read" class="text-sky-200 font-bold">✓✓ Read</span>
                                                <span v-else class="text-indigo-200">✓ Sent</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Selected File Live Preview Bar -->
                            <div v-if="selectedFile" class="px-4 py-2 bg-indigo-50 border-t border-indigo-100 flex items-center justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div v-if="previewType === 'image'" class="w-12 h-12 rounded-lg overflow-hidden shrink-0 border border-indigo-200">
                                        <img :src="previewUrl" class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else-if="previewType === 'video'" class="w-12 h-12 rounded-lg bg-black flex items-center justify-center text-white shrink-0">
                                        🎥
                                    </div>
                                    <div v-else class="w-12 h-12 rounded-lg bg-indigo-200 text-indigo-800 flex items-center justify-center shrink-0">
                                        📄
                                    </div>
                                    <div class="truncate text-xs">
                                        <p class="font-bold text-gray-900 truncate">{{ selectedFile.name }}</p>
                                        <p class="text-gray-500">{{ (selectedFile.size / (1024 * 1024)).toFixed(2) }} MB</p>
                                    </div>
                                </div>
                                <button @click="clearSelectedFile" class="text-red-500 hover:text-red-700 font-bold text-sm p-1.5 rounded-full hover:bg-red-50">
                                    ✕
                                </button>
                            </div>

                            <!-- Chat Input Form -->
                            <div class="p-3 border-t border-gray-200 bg-white">
                                <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                                    <!-- Hidden File Input -->
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/*,video/*,.pdf,.doc,.docx,.zip"
                                        class="hidden"
                                        @change="handleFileSelect"
                                    />

                                    <!-- Media Attachment Button -->
                                    <button
                                        type="button"
                                        @click="triggerFileInput"
                                        class="p-2.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition"
                                        title="Attach Image or Video"
                                    >
                                        📎
                                    </button>

                                    <!-- Message Text Area -->
                                    <input
                                        v-model="form.message"
                                        type="text"
                                        placeholder="Write a message..."
                                        class="flex-1 px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-600 transition"
                                        @keydown.enter.exact.prevent="sendMessage"
                                    />

                                    <!-- AI Polish Button -->
                                    <button
                                        type="button"
                                        @click="polishChatMessage"
                                        :disabled="isPolishingMessage || !form.message.trim()"
                                        class="px-3.5 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-semibold rounded-xl text-xs shadow-sm transition disabled:opacity-40 flex items-center gap-1.5 shrink-0"
                                        title="Make response polite and professional with AI"
                                    >
                                        <span v-if="isPolishingMessage" class="animate-spin text-sm">🌀</span>
                                        <span v-else>✨</span>
                                        <span>AI Polish</span>
                                    </button>

                                    <!-- Send Button -->
                                    <button
                                        type="submit"
                                        :disabled="form.processing || (!form.message.trim() && !form.file)"
                                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl text-sm shadow-md transition disabled:opacity-50 flex items-center gap-1 shrink-0"
                                    >
                                        <span v-if="form.processing">Sending...</span>
                                        <span v-else>Send 🚀</span>
                                    </button>
                                </form>
                            </div>
                        </template>

                        <!-- Empty State when no active conversation -->
                        <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-slate-50/50">
                            <div class="w-20 h-20 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-3xl mb-4 shadow-sm">
                                💬
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1">Select a Conversation</h3>
                            <p class="text-sm text-gray-500 max-w-sm">
                                Select an active chat from the left sidebar or switch to Contacts to connect with a mentor or intern.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal Popup for Image Preview -->
        <div
            v-if="lightboxMedia"
            class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
            @click.self="closeLightbox"
        >
            <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center">
                <button
                    @click="closeLightbox"
                    class="absolute -top-10 right-0 text-white hover:text-gray-300 font-bold text-xl p-2"
                >
                    ✕ Close
                </button>
                <img
                    v-if="lightboxMedia.type === 'image'"
                    :src="lightboxMedia.url"
                    :alt="lightboxMedia.name"
                    class="max-h-[80vh] max-w-full rounded-xl object-contain shadow-2xl"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
