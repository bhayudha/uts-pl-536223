<template>
  <div style="max-width: 800px; margin: 30px auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <h2>Manajemen Data Buku</h2>
      <button @click="handleLogout" style="padding: 8px 15px; background: #ff4d4d; color: white; border: none; border-radius: 4px; cursor: pointer;">
        Logout
      </button>
    </div>

    <!-- Form Tambah / Edit Buku -->
    <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
      <h3>{{ isEditing ? 'Edit Buku' : 'Tambah Buku Baru' }}</h3>
      <form @submit.prevent="saveBuku">
        <input v-model="form.judul" placeholder="Judul Buku" required style="margin: 5px; padding: 8px;" />
        <input v-model="form.penulis" placeholder="Penulis" required style="margin: 5px; padding: 8px;" />
        <input v-model="form.penerbit" placeholder="Penerbit" required style="margin: 5px; padding: 8px;" />
        <input v-model.number="form.tahun_terbit" type="number" placeholder="Tahun Terbit" required style="margin: 5px; padding: 8px;" />
        <input v-model.number="form.harga" type="number" placeholder="Harga" required style="margin: 5px; padding: 8px;" />
        
        <button type="submit" style="margin: 5px; padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
          {{ isEditing ? 'Update' : 'Simpan' }}
        </button>
        <button v-if="isEditing" @click="resetForm" type="button" style="margin: 5px; padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">
          Batal
        </button>
      </form>
    </div>

    <!-- Tabel Daftar Buku -->
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr style="background: #eee;">
          <th>Judul</th>
          <th>Penulis</th>
          <th>Penerbit</th>
          <th>Tahun</th>
          <th>Harga</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in bukuList" :key="item.id">
          <td>{{ item.judul }}</td>
          <td>{{ item.penulis }}</td>
          <td>{{ item.penerbit }}</td>
          <td>{{ item.tahun_terbit }}</td>
          <td>Rp {{ Number(item.harga).toLocaleString() }}</td>
          <td>
            <button @click="editBuku(item)" style="margin-right: 5px;">Edit</button>
            <button @click="deleteBuku(item.id)" style="color: red;">Hapus</button>
          </td>
        </tr>
        <tr v-if="bukuList.length === 0">
          <td colspan="6" style="text-align: center;">Belum ada data buku.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();
const bukuList = ref([]);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
  judul: '',
  penulis: '',
  penerbit: '',
  tahun_terbit: '',
  harga: ''
});

const fetchBuku = async () => {
  try {
    const response = await api.get('/buku');
    bukuList.value = response.data;
  } catch (error) {
    if (error.response?.status === 401) {
      handleLogout();
    }
  }
};

const saveBuku = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/buku/${editingId.value}`, form.value);
    } else {
      await api.post('/buku', form.value);
    }
    resetForm();
    fetchBuku();
  } catch (error) {
    alert('Gagal menyimpan data');
  }
};

const editBuku = (item) => {
  isEditing.value = true;
  editingId.value = item.id;
  form.value = { ...item };
};

const deleteBuku = async (id) => {
  if (confirm('Yakin ingin menghapus buku ini?')) {
    try {
      await api.delete(`/buku/${id}`);
      fetchBuku();
    } catch (error) {
      alert('Gagal menghapus data');
    }
  }
};

const resetForm = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = { judul: '', penulis: '', penerbit: '', tahun_terbit: '', harga: '' };
};

const handleLogout = async () => {
  try {
    await api.post('/logout');
  } catch (e) {
    // Biarkan tetap hapus token lokal jika error
  }
  localStorage.removeItem('token');
  router.push('/login');
};

onMounted(fetchBuku);
</script>