```sql
-- Check if user A (wp_user_id = 2) is a friend of user B (member_id = 3)
SELECT COUNT(\*) FROM wp_cospend_members
WHERE( created_by = 2 AND id = 3)

SELECT COUNT(\*) FROM wp_cospend_members
if WHERE( created_by = 2 AND id = 3) exist, get this row.wp_user_id as friend_id
then

OR (wp_user_id = 2 AND created_by = 3)

we need to count the row counts that match any of these conditions:

1. WHERE( created_by = 2 AND id = 3)
2. WHERE( wp_user_id = 2 AND created_by = friend_wp_user_id) here friend_wp_user_id is row.wp_user_id when condition 1 is true

SELECT COUNT(\*) AS total_count
FROM wp_cospend_members
WHERE
(created_by = 2 AND id = 3)
OR
(
wp_user_id = 2
AND created_by = (
SELECT wp_user_id
FROM wp_cospend_members
WHERE created_by = 2 AND id = 3
LIMIT 1
)
);

-- fail test
SELECT COUNT(\*) AS total_count
FROM wp_cospend_members
WHERE
(created_by = 2 AND id = 1)
OR
(
wp_user_id = 2
AND created_by = (
SELECT wp_user_id
FROM wp_cospend_members
WHERE created_by = 2 AND id = 1
LIMIT 1
)
)
OR
(
created_by = (
SELECT wp_user_id
FROM wp_cospend_members
WHERE id = 1
LIMIT 1
)
AND wp_user_id = 2
)
;

-- check user A and member B are in the same group
-- we have group_id, wp_user_id (User A), member_id (User B) and table name is wp_cospend_group_members

1. check if User B is a member of the group where group_id = group_id
2. Get all the member ids where wp_user_id = User A
3. Check if any of these member ids are in the list of member ids where group_id = group_id

-- Chat gpt solution

-- Replace the values below
-- :group_id => the group you’re checking in
-- :user_a_id => wp_user_id of User A
-- :user_b_mid => member_id of User B

SELECT EXISTS (
SELECT 1
FROM wp_cospend_group_members AS gm_a
JOIN wp_cospend_group_members AS gm_b
ON gm_a.member_id = gm_b.member_id
WHERE
gm_a.wp_user_id = :user_a_id
AND gm_b.group_id = :group_id
AND gm_b.member_id = :user_b_mid
LIMIT 1
) AS user_a_and_b_share_group;

-- replaced values
SELECT EXISTS (
SELECT 1
FROM wp_cospend_group_members AS gm_a
JOIN wp_cospend_group_members AS gm_b
ON gm_a.member_id = gm_b.member_id
WHERE
gm_a.wp_user_id = 2
AND gm_b.group_id = 1
AND gm_b.member_id = 3
LIMIT 1
) AS user_a_and_b_share_group;
```
