import axios from "axios";

const API_URL = "http://localhost:8000/wp-json/wp/v2";

export const authService = {
  login: async (token: string) => {
    const response = await axios.get(`${API_URL}/users/me`, {
      headers: {
        Authorization: `Basic ${token}`,
      },
      params: {
        context: "edit",
      },
    });
    return response.data;
  },

  getMe: async () => {
    const token = localStorage.getItem("token");
    if (!token) {
      throw new Error("No token found");
    }

    const response = await axios.get(`${API_URL}/users/me`, {
      headers: {
        Authorization: `Basic ${token}`,
      },
      params: {
        context: "edit",
      },
    });
    return response.data;
  },
};

export type User = {
  id: number;
  name: string;
  first_name: string;
  last_name: string;
  email: string;
  description: string;
  slug: string;
  avatar_urls: {
    "24": string;
    "48": string;
    "96": string;
  };
  meta: [];
};
