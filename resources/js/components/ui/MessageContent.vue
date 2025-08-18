<template>
  <span class="message-content">
    {{ displayContent }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  content: {
    type: String,
    required: true
  },
  maxLength: {
    type: Number,
    default: null
  }
})

// Fonction pour extraire le contenu du message
const displayContent = computed(() => {
  if (!props.content) return ''
  
  let messageText = props.content
  
  // Essayer de parser le JSON si c'est une chaîne JSON
  try {
    const parsed = JSON.parse(props.content)
    if (parsed && typeof parsed === 'object') {
      // Si c'est un objet avec une propriété 'content', l'utiliser
      if (parsed.content && typeof parsed.content === 'string') {
        messageText = parsed.content
      }
      // Si c'est un objet avec une propriété 'text', l'utiliser
      else if (parsed.text && typeof parsed.text === 'string') {
        messageText = parsed.text
      }
      // Si c'est un objet avec une propriété 'message', l'utiliser
      else if (parsed.message && typeof parsed.message === 'string') {
        messageText = parsed.message
      }
      // Sinon, essayer de convertir l'objet en chaîne lisible
      else {
        messageText = JSON.stringify(parsed, null, 2)
      }
    }
  } catch (e) {
    // Si ce n'est pas du JSON valide, utiliser le contenu tel quel
    messageText = props.content
  }
  
  // Appliquer la limite de longueur si spécifiée
  if (props.maxLength && messageText.length > props.maxLength) {
    messageText = messageText.substring(0, props.maxLength) + '...'
  }
  
  return messageText
})
</script>

<style scoped>
.message-content {
  word-break: break-word;
  overflow-wrap: break-word;
}
</style>

