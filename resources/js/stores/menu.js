import { ref, computed } from 'vue';
import axios from 'axios';

const menus = ref([]);
const currentMenu = ref(null);
const isLoading = ref(false);

export default function useMenuStore() {
  const getMenus = computed(() => menus.value);
  const getCurrentMenu = computed(() => currentMenu.value);

  const fetchMenus = async (params = {}) => {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/menus', { params });
      menus.value = response.data.menus.data || response.data.menus;
      return response.data;
    } catch (error) {
      console.error('Error fetching menus:', error);
      throw error;
    } finally {
      isLoading.value = false;
    }
  };

  const fetchMenu = async (id) => {
    isLoading.value = true;
    try {
      const response = await axios.get(`/api/menus/${id}`);
      currentMenu.value = response.data.menu;
      return response.data.menu;
    } catch (error) {
      console.error('Error fetching menu:', error);
      throw error;
    } finally {
      isLoading.value = false;
    }
  };

  const createMenu = async (menuData) => {
    try {
      const response = await axios.post('/api/menus', menuData);
      return response.data.menu;
    } catch (error) {
      console.error('Error creating menu:', error);
      throw error;
    }
  };

  const updateMenu = async (id, menuData) => {
    try {
      const response = await axios.put(`/api/menus/${id}`, menuData);

      // Update current menu if it's the one being updated
      if (currentMenu.value && currentMenu.value.id === id) {
        currentMenu.value = response.data.menu;
      }

      // Update in menus list if it exists
      const index = menus.value.findIndex(menu => menu.id === id);
      if (index !== -1) {
        menus.value[index] = response.data.menu;
      }

      return response.data.menu;
    } catch (error) {
      console.error('Error updating menu:', error);
      throw error;
    }
  };

  const deleteMenu = async (id) => {
    try {
      await axios.delete(`/api/menus/${id}`);

      // Remove from menus list
      menus.value = menus.value.filter(menu => menu.id !== id);

      // Clear current menu if it's the one being deleted
      if (currentMenu.value && currentMenu.value.id === id) {
        currentMenu.value = null;
      }
    } catch (error) {
      console.error('Error deleting menu:', error);
      throw error;
    }
  };

  const duplicateMenu = async (id) => {
    try {
      const response = await axios.post(`/api/menus/${id}/duplicate`);
      return response.data.menu;
    } catch (error) {
      console.error('Error duplicating menu:', error);
      throw error;
    }
  };

  const getCostBreakdown = async (id) => {
    try {
      const response = await axios.get(`/api/menus/${id}/cost-breakdown`);
      return response.data.breakdown;
    } catch (error) {
      console.error('Error fetching cost breakdown:', error);
      throw error;
    }
  };

  const addSegment = async (segmentData) => {
    try {
      const response = await axios.post('/api/menu-segments', segmentData);
      return response.data.segment;
    } catch (error) {
      console.error('Error adding segment:', error);
      throw error;
    }
  };

  const updateSegment = async (segmentId, segmentData) => {
    try {
      const response = await axios.put(`/api/menu-segments/${segmentId}`, segmentData);
      return response.data.segment;
    } catch (error) {
      console.error('Error updating segment:', error);
      throw error;
    }
  };

  const deleteSegment = async (segmentId) => {
    try {
      await axios.delete(`/api/menu-segments/${segmentId}`);
    } catch (error) {
      console.error('Error deleting segment:', error);
      throw error;
    }
  };

  const addItemToSegment = async (segmentId, itemData) => {
    try {
      const response = await axios.post(`/api/menu-segments/${segmentId}/items`, itemData);
      return response.data.item;
    } catch (error) {
      console.error('Error adding item to segment:', error);
      throw error;
    }
  };

  const updateSegmentItem = async (itemId, itemData) => {
    try {
      const response = await axios.put(`/api/menu-segment-items/${itemId}`, itemData);
      return response.data.item;
    } catch (error) {
      console.error('Error updating segment item:', error);
      throw error;
    }
  };

  const removeItemFromSegment = async (itemId) => {
    try {
      await axios.delete(`/api/menu-segment-items/${itemId}`);
    } catch (error) {
      console.error('Error removing item from segment:', error);
      throw error;
    }
  };

  const reorderSegments = async (menuId, segments) => {
    try {
      await axios.post('/api/menu-segments/reorder', {
        menu_id: menuId,
        segments: segments
      });
    } catch (error) {
      console.error('Error reordering segments:', error);
      throw error;
    }
  };

  const reorderSegmentItems = async (segmentId, items) => {
    try {
      await axios.post(`/api/menu-segments/${segmentId}/items/reorder`, {
        items: items
      });
    } catch (error) {
      console.error('Error reordering segment items:', error);
      throw error;
    }
  };

  const clearCurrentMenu = () => {
    currentMenu.value = null;
  };

  return {
    // State
    menus: getMenus,
    currentMenu: getCurrentMenu,
    isLoading: computed(() => isLoading.value),

    // Actions
    fetchMenus,
    fetchMenu,
    createMenu,
    updateMenu,
    deleteMenu,
    duplicateMenu,
    getCostBreakdown,
    addSegment,
    updateSegment,
    deleteSegment,
    addItemToSegment,
    updateSegmentItem,
    removeItemFromSegment,
    reorderSegments,
    reorderSegmentItems,
    clearCurrentMenu,
  };
}
