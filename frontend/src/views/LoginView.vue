<template>
  <div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2>Login System Buku</h2>
    <form @submit.prevent="handleLogin">
      <div style="margin-bottom: 15px;">
        <label>Email:</label>
        <input type="email" v-model="email" required style="width: 100%; padding: 8px; margin-top: 5px;" />
      </div>
      <div style="margin-bottom: 15px;">
        <label>Password:</label>
        <input type="password" v-model="password" required style="width: 100%; padding: 8px; margin-top: 5px;" />
      </div>
      <button type="submit" style="width: 100%; padding: 10px; background: #42b883; color: white; border: none; border-radius: 4px; cursor: pointer;">
        Login
      </button>
    </form>
    <p v-if="errorMessage" style="color: red; margin-top: 15px;">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const email = ref('pralambang90@gmail.com');
const password = ref('password123');
const errorMessage = ref('');
const router = useRouter();

const handleLogin = async () => {
  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    });
    
    // Simpan token ke localStorage
    localStorage.setItem('token', response.data.access_token);
    router.push('/buku');
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login Gagal';
  }
};
</script>