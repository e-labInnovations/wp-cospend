import {
  createBrowserRouter,
  createRoutesFromElements,
  Route,
} from "react-router-dom";
import Layout from "@/pages/layout";
import Home from "@/pages/page";
import Accounts from "@/pages/accounts/page";
import AddTransaction from "@/pages/add-transaction/page";
import Categories from "@/pages/categories/page";
import Currencies from "@/pages/currencies/page";
import Groups from "@/pages/groups/page";
import Group from "@/pages/groups/[id]/page";
import Person from "@/pages/people/[id]/page";
import Settings from "@/pages/settings/page";
import Tags from "@/pages/tags/page";
import Transactions from "@/pages/transactions/page";
import Transaction from "@/pages/transactions/[id]/page";
import Extra from "@/pages/extra/page";
import Profile from "@/pages/settings/profile/page";
import AddAccount from "@/pages/accounts/add/page";
import AddCategory from "@/pages/categories/add/page";
import AddTag from "@/pages/tags/add/page";
import EditTransaction from "@/pages/transactions/[id]/edit/page";
import EditPerson from "@/pages/people/[id]/edit/page";
import AddPerson from "@/pages/people/add/page";
import AddGroup from "@/pages/groups/add/page";
import EditGroup from "@/pages/groups/[id]/edit/page";

export const router = createBrowserRouter(
  createRoutesFromElements(
    <Route element={<Layout />}>
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
  )
);
