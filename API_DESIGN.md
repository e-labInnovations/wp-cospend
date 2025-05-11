# WP-Cospend API & Database Design

## Overview

WP-Cospend is a WordPress-based expense tracking application that allows users to manage shared expenses, track balances, and manage groups. The application integrates with WordPress for authentication and user management while maintaining its own data structure for expense tracking.

## Database Schema

### WordPress Core Tables (Existing)

- `wp_users` - WordPress users table
- `wp_usermeta` - WordPress user metadata (used for storing default currency)

### Custom Tables

#### wp_cospend_groups

```sql
CREATE TABLE wp_cospend_groups (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES wp_users(ID)
);
```

#### wp_cospend_members

```sql
CREATE TABLE wp_cospend_members (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    avatar_url VARCHAR(255),
    wp_user_id BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id),
    FOREIGN KEY (wp_user_id) REFERENCES wp_users(ID)
);
```

- `wp_user_id` is nullable. If set, use the actual WP user's data (name, email, avatar, etc). If null, use the data from this table.
- This allows you to "link" a member to a real user later.

#### wp_cospend_group_members

```sql
CREATE TABLE wp_cospend_group_members (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id),
    FOREIGN KEY (member_id) REFERENCES wp_cospend_members(id),
    UNIQUE KEY unique_group_member (group_id, member_id)
);
```

#### wp_cospend_transactions

```sql
CREATE TABLE wp_cospend_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED,
    currency VARCHAR(3) DEFAULT 'USD',
    PRIMARY KEY (id),
    FOREIGN KEY (group_id) REFERENCES wp_cospend_groups(id),
    FOREIGN KEY (created_by) REFERENCES wp_users(ID),
    FOREIGN KEY (category_id) REFERENCES wp_cospend_categories(id)
);
```

#### wp_cospend_transaction_splits

```sql
CREATE TABLE wp_cospend_transaction_splits (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaction_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    is_paid BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (transaction_id) REFERENCES wp_cospend_transactions(id),
    FOREIGN KEY (member_id) REFERENCES wp_cospend_members(id)
);
```

#### wp_cospend_categories

```sql
CREATE TABLE wp_cospend_categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(50),
    color VARCHAR(7),
    created_by BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES wp_users(ID)
);
```

## Currencies

- The list of supported currencies is hardcoded in the backend/plugin code.
- Each currency has a code, symbol, and name.
- Example:

```json
[
  { "code": "USD", "symbol": "$", "name": "US Dollar" },
  { "code": "EUR", "symbol": "€", "name": "Euro" },
  { "code": "GBP", "symbol": "£", "name": "British Pound" }
]
```

- The user's default currency is stored in `wp_usermeta` with a key like `cospend_default_currency`.

## REST API Endpoints

### Authentication

- `POST /wp-json/wp/v2/users/me` - Get current user info
- Authentication is handled via WordPress application passwords, which can be generated from the WordPress dashboard. No separate login API is required.

### Groups

- `GET /wp-json/cospend/v1/groups` - List all groups
- `POST /wp-json/cospend/v1/groups` - Create new group
- `GET /wp-json/cospend/v1/groups/{id}` - Get group details
- `PUT /wp-json/cospend/v1/groups/{id}` - Update group
- `DELETE /wp-json/cospend/v1/groups/{id}` - Delete group
- `GET /wp-json/cospend/v1/groups/{id}/members` - List group members (now returns members from wp_cospend_members)
- `POST /wp-json/cospend/v1/groups/{id}/members` - Add member to group (can add by name/email or link to wp_user_id)
- `PUT /wp-json/cospend/v1/groups/{id}/members/{member_id}/link` - Link a member to a WordPress user
- `PUT /wp-json/cospend/v1/groups/{id}/members/{member_id}/unlink` - Unlink a member from a WordPress user
- `DELETE /wp-json/cospend/v1/groups/{id}/members/{member_id}` - Remove member

### Members

- `GET /wp-json/cospend/v1/groups/{group_id}/members` - List all members in a group
- `POST /wp-json/cospend/v1/groups/{group_id}/members` - Add a new member (with or without linking to a WP user)
- `PUT /wp-json/cospend/v1/groups/{group_id}/members/{member_id}` - Update member info
- `PUT /wp-json/cospend/v1/groups/{group_id}/members/{member_id}/link` - Link member to WP user
- `PUT /wp-json/cospend/v1/groups/{group_id}/members/{member_id}/unlink` - Unlink member from WP user
- `DELETE /wp-json/cospend/v1/groups/{group_id}/members/{member_id}` - Remove member

### Transactions

- `GET /wp-json/cospend/v1/transactions` - List transactions
- `POST /wp-json/cospend/v1/transactions` - Create transaction
- `GET /wp-json/cospend/v1/transactions/{id}` - Get transaction details
- `PUT /wp-json/cospend/v1/transactions/{id}` - Update transaction
- `DELETE /wp-json/cospend/v1/transactions/{id}` - Delete transaction
- `GET /wp-json/cospend/v1/transactions/summary` - Get expense summary

### Categories

- `GET /wp-json/cospend/v1/categories` - List categories
- `POST /wp-json/cospend/v1/categories` - Create category
- `PUT /wp-json/cospend/v1/categories/{id}` - Update category
- `DELETE /wp-json/cospend/v1/categories/{id}` - Delete category

### Currencies

- `GET /wp-json/cospend/v1/currencies` - List supported currencies and the user's default currency
- `PUT /wp-json/cospend/v1/currencies/default` - Set the user's default currency

## Data Models

### User

```typescript
interface User {
  id: number;
  name: string;
  first_name: string;
  last_name: string;
  email: string;
  description: string;
  avatar_urls: {
    "24": string;
    "48": string;
    "96": string;
  };
}
```

### Member

```typescript
interface Member {
  id: number;
  group_id: number;
  name: string;
  email?: string;
  avatar_url?: string;
  wp_user_id?: number | null;
  // If wp_user_id is set, use WP user data for display
}
```

### Group

```typescript
interface Group {
  id: number;
  name: string;
  description: string;
  icon: string;
  members: number;
  balance: number;
  created_at: string;
  updated_at: string;
  created_by: number;
}
```

### Transaction

```typescript
interface Transaction {
  id: number;
  group_id: number;
  amount: number;
  description: string;
  date: string;
  created_by: number;
  category_id: number;
  currency: string;
  splits: TransactionSplit[];
}
```

### TransactionSplit

```typescript
interface TransactionSplit {
  id: number;
  transaction_id: number;
  member_id: number;
  amount: number;
  is_paid: boolean;
}
```

### Category

```typescript
interface Category {
  id: number;
  name: string;
  icon: string;
  color: string;
  created_by: number;
}
```

### Currency

```typescript
interface Currency {
  code: string;
  symbol: string;
  name: string;
  is_default: boolean;
}
```

## WordPress Integration

### Authentication

- Uses WordPress application passwords for API authentication
- Application passwords can be generated from the WordPress dashboard
- Stores token in localStorage for frontend authentication
- Implements token refresh mechanism

### User & Member Management

- Leverages WordPress user system for authentication and profile data
- Allows adding group members who are not WP users
- Members can be linked/unlinked to actual WP users
- If a member is linked, always use WP user data for display; otherwise, use member table data
- Uses WordPress avatar system if linked
- Stores default currency in `wp_usermeta` as `cospend_default_currency`

### Permissions

- Implements custom capabilities for expense management
- Group-based access control
- Role-based permissions within groups

## Security Considerations

1. **Authentication**

   - Use WordPress nonces for form submissions
   - Implement rate limiting for API endpoints
   - Secure token storage and transmission

2. **Data Validation**

   - Sanitize all input data
   - Validate data types and ranges
   - Implement proper error handling

3. **Access Control**

   - Verify user permissions for each action
   - Implement group membership checks
   - Validate user ownership of resources

4. **API Security**
   - Use HTTPS for all API calls
   - Implement proper CORS policies
   - Add request validation middleware

## Performance Considerations

1. **Database Optimization**

   - Index frequently queried columns
   - Implement caching for common queries
   - Use proper foreign key constraints

2. **API Optimization**

   - Implement pagination for list endpoints
   - Use proper HTTP caching headers
   - Optimize response payload size

3. **Frontend Optimization**
   - Implement data caching
   - Use optimistic updates
   - Implement proper loading states

## Use Cases

- **Add member**: User adds "Alice" with just a name/email (no WP account).
- **Link member**: Later, Alice registers. User links Alice's member record to her WP user account.
- **Display**: If linked, always show Alice's WP profile info; if not, show the info from the member table.
- **Unlink member**: If needed, unlink a member from a WP user, reverting to the member table data.
- **Set default currency**: User sets their preferred currency, which is stored in usermeta.
