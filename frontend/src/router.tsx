import {
  createBrowserRouter,
  createRoutesFromElements,
  Route,
} from "react-router-dom";
import Layout from "@/pages/layout";
import Home from "@/pages/home-page";
import Accounts from "@/pages/accounts/accounts-page";
import AddTransaction from "@/pages/transactions/add-transaction-page";
import Categories from "@/pages/categories/categories-page";
import Currencies from "@/pages/currencies/currencies-page";
import Groups from "@/pages/groups/groups-people-page";
import Group from "@/pages/groups/group-page";
import Person from "@/pages/person/person-page";
import Settings from "@/pages/settings/settings-page";
import Tags from "@/pages/tags/tags-page";
import Transactions from "@/pages/transactions/transactions-page";
import Transaction from "@/pages/transactions/transaction-page";
import Extra from "@/pages/extra/extra-page";
import Profile from "@/pages/settings/profile-page";
import AddAccount from "@/pages/accounts/add-account-page";
import AddCategory from "@/pages/categories/add-category-page";
import AddTag from "@/pages/tags/add-tag-page";
import EditTransaction from "@/pages/transactions/edit-transaction-page";
import EditPerson from "@/pages/person/edit-person-page";
import AddPerson from "@/pages/person/add-person-page";
import AddGroup from "@/pages/groups/add-group-page";
import EditGroup from "@/pages/groups/edit-group-page";
import LoginPage from "@/pages/login/login-page";
import ProtectedRoute from "@/components/protected-route";

export const router = createBrowserRouter(
  createRoutesFromElements(
    <>
      <Route path="/login" element={<LoginPage />} />
      <Route
        element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }
      >
        <Route index element={<Home />} />
        <Route path="transactions" element={<Transactions />} />
        <Route path="transactions/:id" element={<Transaction />} />
        <Route path="transactions/:id/edit" element={<EditTransaction />} />
        <Route path="add-transaction" element={<AddTransaction />} />
        <Route path="groups" element={<Groups />} />
        <Route path="groups/add" element={<AddGroup />} />
        <Route path="groups/:id" element={<Group />} />
        <Route path="groups/:id/edit" element={<EditGroup />} />
        <Route path="people" element={<Groups />} />
        <Route path="people/add" element={<AddPerson />} />
        <Route path="people/:id" element={<Person />} />
        <Route path="people/:id/edit" element={<EditPerson />} />
        <Route path="extra" element={<Extra />} />
        <Route path="categories" element={<Categories />} />
        <Route path="categories/add" element={<AddCategory />} />
        <Route path="tags" element={<Tags />} />
        <Route path="tags/add" element={<AddTag />} />
        <Route path="currencies" element={<Currencies />} />
        <Route path="accounts" element={<Accounts />} />
        <Route path="accounts/add" element={<AddAccount />} />
        <Route path="settings" element={<Settings />} />
        <Route path="settings/profile" element={<Profile />} />
      </Route>
    </>
  )
);
