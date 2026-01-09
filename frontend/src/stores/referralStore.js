import { defineStore } from 'pinia';
import { ref } from 'vue';
import axiosInstance from '@/axios';

export const useReferralStore = defineStore('referral', () => {
  const userReferralCode = ref('');
  const creatorReferralCode = ref('');
  const totalEarnings = ref(0);
  const totalClaims = ref(0);
  const loading = ref(false);
  const error = ref(null);
  const userReferralLink = ref('');
  const creatorReferralLink = ref('');

  const fetchReferralData = async () => {
    loading.value = true;
    error.value = null;
    try {
      // Get statistics
      const statsResponse = await axiosInstance.get('/referrals/statistics');
      const stats = statsResponse.data.data;
      
      // Get referral link
      const linkResponse = await axiosInstance.post('/referrals/generate-link');
      const linkData = linkResponse.data.data;
      
      userReferralCode.value = linkData.referral_code;
      creatorReferralCode.value = linkData.referral_code; // Both user and creator use the same code
      totalEarnings.value = stats.total_earnings;
      totalClaims.value = stats.total_referrals;

      // Store the actual referral links from the backend
      if (linkData.referral_links) {
        userReferralLink.value = linkData.referral_links.user;
        creatorReferralLink.value = linkData.referral_links.creator;
      }

      // Log the referral links from the backend
      if (statsResponse.data && statsResponse.data.data && statsResponse.data.data.referral_links) {
        console.log('Referral links from backend:', statsResponse.data.data.referral_links);
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch referral data';
      console.error('Error fetching referral data:', err);
    } finally {
      loading.value = false;
    }
  };

  const generateUserReferralCode = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axiosInstance.post('/referrals/generate-link');
      userReferralCode.value = response.data.data.referral_code;
      return response.data.data.referral_code;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to generate user referral code';
      console.error('Error generating user referral code:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const generateCreatorReferralCode = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axiosInstance.post('/referrals/generate-creator-code');
      creatorReferralCode.value = response.data.data.referral_code;
      return response.data.data.referral_code;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to generate creator referral code';
      console.error('Error generating creator referral code:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateUserReferralCode = async (newCode) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axiosInstance.put('/referrals/update-code', {
        code: newCode,
        type: 'user'
      });
      userReferralCode.value = response.data.data.referral_code;
      return response.data.data.referral_code;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update user referral code';
      console.error('Error updating user referral code:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateCreatorReferralCode = async (newCode) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axiosInstance.put('/referrals/update-code', {
        code: newCode,
        type: 'creator'
      });
      creatorReferralCode.value = response.data.data.referral_code;
      return response.data.data.referral_code;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update creator referral code';
      console.error('Error updating creator referral code:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const getReferralLinks = () => {
    return {
      userReferralLink: userReferralLink.value,
      creatorReferralLink: creatorReferralLink.value
    };
  };

  return {
    userReferralCode,
    creatorReferralCode,
    totalEarnings,
    totalClaims,
    loading,
    error,
    fetchReferralData,
    generateUserReferralCode,
    generateCreatorReferralCode,
    updateUserReferralCode,
    updateCreatorReferralCode,
    userReferralLink,
    creatorReferralLink,
    getReferralLinks
  };
}); 