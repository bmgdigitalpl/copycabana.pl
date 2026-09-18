<div x-data="ccServiceChat()" @open-service-chat.window="openFor($event.detail.title, $event.detail.topic, $event.detail.questions)">
  <div class="cc-chat-overlay" :class="{ open: open }" @click="close()"></div>
  <div class="cc-chat-panel" :class="{ open: open }" role="dialog" aria-modal="true" aria-label="CopyCabana Chat">
    <div class="cc-chat-header">
      <div class="cc-chat-header-text">
        <span class="cc-chat-title">CopyCabana Chat</span>
        <span class="cc-chat-subtitle">Pytanie o: <strong x-text="topic"></strong></span>
      </div>
      <button type="button" class="cc-chat-close" @click="close()" aria-label="Zamknij czat"><i class="fas fa-times" aria-hidden="true"></i></button>
    </div>
    <div class="cc-chat-messages" x-ref="messages">
      <template x-if="! messages.length">
        <div class="cc-chat-empty">
          <i class="fas fa-comment-dots" aria-hidden="true"></i>
          <p>Zadaj swoje pytanie o <strong x-text="topic"></strong></p>
        </div>
      </template>
      <template x-for="msg in messages" :key="msg.id">
        <div class="cc-chat-bubble" :class="msg.role" x-text="msg.text"></div>
      </template>
    </div>
    <template x-if="suggestedQuestions.length">
      <div class="cc-chat-suggestions">
        <template x-for="q in suggestedQuestions" :key="q">
          <button type="button" class="cc-chat-suggestion" @click="ask(q)">
            <i class="fas fa-comment-dots" aria-hidden="true"></i>
            <span x-text="q"></span>
          </button>
        </template>
      </div>
    </template>
    <form class="cc-chat-form" @submit.prevent="send()">
      <input type="text" x-model="draft" placeholder="Zadaj pytanie…" autocomplete="off" aria-label="Twoja wiadomość">
      <button type="submit" class="btn-magenta" aria-label="Wyślij"><i class="fas fa-paper-plane" aria-hidden="true"></i></button>
    </form>
  </div>
</div>
