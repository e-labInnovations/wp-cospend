import {
  createContext,
  useEffect,
  useMemo,
  useReducer,
  type PropsWithChildren,
} from "react";
import { type User, authService } from "@/services/api/auth";

interface AuthContextType {
  user: User | null;
  signIn: (token: string, user: User) => Promise<void>;
  signOut: () => void;
  loading: boolean;
}

type AuthProviderProps = PropsWithChildren;
type ReducerAction =
  | {
      type: "SIGN_IN";
      payload: { user: User; token: string };
    }
  | { type: "SIGN_OUT" }
  | { type: "SET_LOADING"; payload: { loading: boolean } };

type StateType = {
  user: User | null;
  loading: boolean;
  token: string | null;
};

const initialState: StateType = {
  user: null,
  loading: true,
  token: null,
};

export const AuthContext = createContext<AuthContextType | undefined>(
  undefined
);

const authReducer = (state: StateType, action: ReducerAction): StateType => {
  switch (action.type) {
    case "SIGN_IN":
      return {
        ...state,
        user: action.payload.user,
        token: action.payload.token,
      };
    case "SIGN_OUT":
      return {
        ...state,
        user: null,
        token: null,
      };
    case "SET_LOADING":
      return {
        ...state,
        loading: action.payload.loading,
      };
    default:
      return state;
  }
};

export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [state, dispatch] = useReducer(authReducer, initialState);

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (token) {
      const fetchUserData = async () => {
        try {
          const userData = await authService.getMe();
          dispatch({
            type: "SIGN_IN",
            payload: { user: userData, token: token },
          });
        } catch {
          dispatch({ type: "SIGN_OUT" });
        } finally {
          dispatch({ type: "SET_LOADING", payload: { loading: false } });
        }
      };
      fetchUserData();
    } else {
      dispatch({ type: "SET_LOADING", payload: { loading: false } });
    }
  }, []);

  const signIn = async (token: string, user: User) => {
    dispatch({ type: "SIGN_IN", payload: { user, token } });
    localStorage.setItem("token", token);
  };

  const signOut = () => {
    dispatch({ type: "SIGN_OUT" });
    localStorage.removeItem("token");
  };

  const contextValue = useMemo(
    () => ({
      user: state.user,
      signIn,
      signOut,
      loading: state.loading,
    }),
    [state.user, state.loading]
  );

  return (
    <AuthContext.Provider value={contextValue}>{children}</AuthContext.Provider>
  );
};
