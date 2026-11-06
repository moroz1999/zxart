# Users and user groups

Implementation: [../features/users.md](../features/users.md).

## Users
A user is a visitor's account: a sign-in name, an email, and a few marks the
site shows or acts on. These are whether the email is confirmed, whether the
user may comment, whether the email is shown to others, and the VIP, supporter
and volunteer badges. A user may also have claimed an author, and is then
linked to that author.

No two users share a sign-in name or an email.

## User groups
What a user may do on the site comes from the groups they belong to. A group
has a name and a description; what its members may do is set apart from the
group itself. A change of groups takes effect at the user's next sign-in.
Deleting a group keeps its users and only takes the group away from them.

## Banning
Banning a user deletes every comment and vote they left and stops them from
signing in. It cannot be undone: the comments and votes are gone, and there is
no unban.

## Managing users
Whoever is allowed to manage users sees:

- **an overview**: how many users there are, how many registered in the last 7
  and 30 days, how many are banned and how many never confirmed their email,
  and the registrations of each of the last twelve months;
- **the list of users**, newest first. It is searched by part of the name or
  the email as one types, filtered by group and by whether the user is banned,
  and sorted by name, email or registration date;
- **a user's page**: when they registered, how many comments and votes they
  left, the author they claimed, the form of their account and their groups,
  and the ban.

Managing the groups themselves (adding, renaming, deleting) is allowed apart
from managing users, and so is banning.
