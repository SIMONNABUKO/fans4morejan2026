import axios from "@/axios"

class MessageService {
  getRecentConversations() {
    // Changed from "/messages/conversations" to "/messages"
    return axios.get('/messages')
  }
  
  getOrCreateConversation(userId) {
    // Changed from "/messages/conversations/{userId}" to "/messages/{userId}"
    return axios.get(`/messages/${userId}`)
  }
  
  sendMessage(receiverId, data) {
    // Changed from "/messages/send/{receiverId}" to "/messages"
    // The receiverId is included in the form data
    return axios.post('/messages', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
  
  markAsRead(messageId) {
    return axios.post(`/messages/${messageId}/read`)
  }
  
  getUnreadCount() {
    return axios.get('/messages/unread-count')
  }
  
  unlockMessage(messageId, data) {
    return axios.post(`/messages/${messageId}/unlock`, data)
  }
}

export default new MessageService()