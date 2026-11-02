# Post-installation tutorial

Follow these steps after configuring the application and its database. Replace
example addresses with the real details for your installation.

## 1. Run the migrations and seed the default account

Make sure your `.env` file points to the database you intend to use, then run:

```bash
php artisan migrate
php artisan db:seed
```

The default `DatabaseSeeder` creates the application roles and a Super Admin
account. The roles are **Super Admin**, **Admin**, **Internal User**,
**Customer**, and **Engineer**. The application uses these roles when assigning
access to users.

Use the following credentials for the first login:

| Login field | Default value |
|---|---|
| Email / username | `admin@example.com` |
| Password | `password123` |

**Change the default email and password immediately.** Do not leave the
well-known default credentials on an internet-accessible or shared installation.

### Change the Super Admin email and password

1. Sign in with the default credentials.
2. Open **User Management** from the sidebar and find the **Administrator**
   account.
3. Click the administrator's name to open the profile, then choose **Edit
   details**. Update the name and email address and save with **Update Details**.
   The email address is the login username; there is no separate username field.
4. Sign out and select **Forgot your password?** on the login screen. Enter the
   updated email address and follow the reset link sent to that mailbox to set
   your own password.

Password reset by email requires working mail delivery settings. If mail is not
configured, a user with access to **User Management** can open the account's
profile and choose **Reset password**. This creates a temporary generated
password rather than letting you type one in. Record it before submitting and
share it securely; use the login screen's **Forgot your password?** flow to set
a private password once email delivery is working.

## 2. Synchronise application permissions

After the first login, synchronise the permissions defined by the installed
packages so they are available in the database:

1. Open **User Management**.
2. Select **Permissions**.
3. Select **Synchronise Permissions**.

Alternatively, run the equivalent Artisan command:

```bash
php artisan permissions:sync
```

This adds missing package-defined permissions to the database. It does not
replace assigning users to the appropriate roles.

## 3. Optional: add demo data

To populate sample records for testing, run:

```bash
php artisan db:seed --class=DemoDatabaseSeeder
```

This separate seeder creates:

- 10 sample users in `users`
- 30 sample customers in `customers`
- 20 sample suppliers in `suppliers`
- 20 sample products in `products`

The demo seeder is not run by the regular `php artisan db:seed` command and
refuses to seed when the application environment is `production`. Use it only
with a non-production database.

## 4. Create an application user

1. Open **User Management** from the sidebar.
2. Select **Create New User** and enter the person's name and unique email.
3. The form generates a password and displays it as read-only. Copy it before
   closing the form and deliver it securely.
4. Find the new account in the user list and select **Update Roles** to assign
   the appropriate role. New users are not assigned a role by the creation form.

The email address is the user's login username. Users can use **Forgot your
password?** on the login screen to set a new password if mail delivery is
configured.

## 5. Create a customer contact

Customer contacts are linked to user accounts. To create a contact and its
account together:

1. Open **Customer Contacts** from the sidebar.
2. Select **Create New Contact**.
3. Choose the customer and contact role, then enter the contact's name, email,
   address line 1, and postcode. These fields are required; the other contact
   details are optional.
4. Select **Has Portal Access** if this person should be able to use the
   customer portal, then create the contact.

Creating a new contact also creates a linked user with the **Customer** role.
The contact form generates its password but does not display it. Once mail
delivery is configured, the contact can use **Forgot your password?** on the
login screen to set a password. Alternatively, an administrator can open the
contact's profile and reset the password. Record the generated password before
submitting the reset, then provide it securely.

If the person already has an application account, choose **Add Existing User**
on the **Customer Contacts** page instead. Select the customer, contact role,
and existing user, then enable **Has Portal Access** if needed.

### Enable customer portal access

Portal access needs to be enabled at both the customer and contact levels:

- On the customer's profile, select **Update Details** and enable **Portal
  Enabled**.
- On the contact, enable **Has Portal Access**. The contact should also be
  active.

The customer's **Portal Enabled** setting applies to the customer, while
**Has Portal Access** grants portal access to an individual contact. Both
settings are required for that contact to use the customer portal.
