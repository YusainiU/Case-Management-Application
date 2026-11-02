# Roles and Permissions Guide

This guide explains how roles and permissions are defined, synchronized, assigned, and checked in this application. It is based on the permission enums and providers in `packages/Simplydigital`, the user-management screens, and the database seeders.

## 1. The basic model

- A **permission** is a named capability, such as `case.view` or `customer.create`.
- A **role** is a named collection of permissions, such as `Internal User`.
- A **user** is assigned one or more roles. The user's permissions are then checked when they open a page or perform an action.

The application uses Spatie Laravel Permission. The `App\Models\User` model uses the `HasRoles` trait, and the seeded roles and permissions use the `web` guard.

The role name alone does not grant capabilities. For example, a role named `Admin` is not automatically an administrator unless permissions are assigned to it. The exception is `Super Admin`, which the application explicitly treats as a global authorization bypass (see Section 5).

## 2. Roles created by the application

`database/seeders/RoleSeeder.php` creates these roles with the `web` guard:

| Role | What the seed does |
|---|---|
| Super Admin | Creates the role; the authorization provider gives users with this role a global Gate bypass. |
| Admin | Creates the role only. No permissions are assigned by the seeder. |
| Internal User | Creates the role only. No permissions are assigned by the seeder. |
| Customer | Creates the role only. No permissions are assigned by the seeder. |
| Engineer | Creates the role only. No permissions are assigned by the seeder. |

`DatabaseSeeder` runs `RoleSeeder` and then `UserAdminSeeder`. The latter assigns the `Super Admin` role to the seeded administrator account. It does not configure permission bundles for the other roles.

On a fresh database, `Admin`, `Internal User`, `Customer`, and `Engineer` have no permissions until an authorized user configures them. Because the seeder uses `firstOrCreate`, rerunning it does not clear permissions already attached to existing roles. Do not infer access from role names.

## 3. Where permissions come from

Packages declare permission names in backed enums and expose them through `PermissionProvider` implementations. Providers also supply a description and a display group for each permission. The `PermissionRegistry` gathers these definitions; `PermissionSynchronizer` creates any missing permission records in the database.

To synchronize package-defined permissions, run:

```shell
php artisan permissions:sync
```

The User Management permission screen also offers a synchronization action and compares registered definitions with the database. Synchronizing creates missing permissions; it does not assign them to roles. The synchronizer uses `findOrCreate`, so it does not remove older or manually created database permissions.

Use the exact permission spelling shown below. Permission names are case-sensitive; for example, `User.view` and `user.view` are different names.

## 4. Permission catalogue

These are the names declared by the package enums. The registered permissions are returned by their package providers and are available through the normal synchronization flow. The `case.assign` and `case.close` enum values are the exceptions; they are marked below because they are not currently registered.

### User management

| Permission | Purpose |
|---|---|
| `User.view` | View users |
| `User.create` | Create users |
| `User.update` | Update users |
| `User.delete` | Delete users |
| `Role.view` | View roles |
| `Role.create` | Create roles |
| `Role.update` | Change a role's permissions |
| `Role.delete` | Delete roles |

### Case management

| Permission | Purpose |
|---|---|
| `case.view` | View cases |
| `case.create` | Create cases |
| `case.update` | Update cases |
| `case.delete` | Delete cases |
| `case.assign` | Intended to represent case assignment; **declared in the enum but not currently registered by the package provider** |
| `case.close` | Intended to represent case closure; **declared in the enum but not currently registered by the package provider** |

The current `CasePolicy` checks `case.view`, `case.create`, `case.update`, and `case.delete`. It does not define separate policy abilities for `case.assign` or `case.close`. Those two enum values therefore should not be treated as active, separately enforceable permissions in the normal role-management flow.

### Customer, contacts, and customer portal

| Permission | Purpose |
|---|---|
| `customer.view` | View customers |
| `customer.create` | Create customers |
| `customer.update` | Update customers |
| `customer.delete` | Delete customers |
| `customer_contacts.view` | View customer contacts |
| `customer_contacts.create` | Create customer contacts |
| `customer_contacts.update` | Update customer contacts |
| `customer_contacts.delete` | Delete customer contacts |
| `portal.access` | Enter the customer portal |
| `portal.view-case` | View cases in the portal |
| `portal.create-case` | Create portal cases |
| `portal.comment-case` | Comment on portal cases |

The customer portal middleware explicitly checks `portal.access`. The other portal permissions are registered separately and are intended to control their respective portal actions.

### Case-related records

| Permission | Purpose |
|---|---|
| `service_level_agreement.view` | View service-level agreements |
| `service_level_agreement.create` | Create service-level agreements |
| `service_level_agreement.update` | Update service-level agreements |
| `service_level_agreement.delete` | Delete service-level agreements |
| `product.view` | View products |
| `product.create` | Create products |
| `product.update` | Update products |
| `product.delete` | Delete products |
| `supplier.view` | View suppliers |
| `supplier.create` | Create suppliers |
| `supplier.update` | Update suppliers |
| `supplier.delete` | Delete suppliers |
| `asset.view` | View assets |
| `asset.create` | Create assets |
| `asset.update` | Update assets |
| `asset.delete` | Delete assets |

### Engineer management

| Permission | Purpose |
|---|---|
| `engineer.view` | View engineers |
| `engineer.create` | Create engineers |
| `engineer.update` | Update engineers |
| `engineer.delete` | Delete engineers |
| `skill.view` | View skills |
| `skill.create` | Create skills |
| `skill.update` | Update skills |
| `skill.delete` | Delete skills |
| `certificate.view` | View certificates |
| `certificate.create` | Create certificates |
| `certificate.update` | Update certificates |
| `certificate.delete` | Delete certificates |

### Other package permissions

| Area | Permission names |
|---|---|
| AI case assistant | `ai.case_assistant.view`, `ai.case_assistant.analyze`, `ai.case_assistant.apply` |
| Expenses | `expense.view`, `expense.create`, `expense.update`, `expense.delete`, `expense.admin` |
| Files | `file.view`, `file.create`, `file.update`, `file.delete` |
| Events | `event.view`, `event.create`, `event.update`, `event.delete` |
| Notifications | `notification.view`, `notification.create`, `notification.update`, `notification.delete` |
| Stock requests | `stock_request.manage` |

## 5. How the application checks access

1. A user signs in and Laravel resolves their `App\Models\User`.
2. The user has one or more roles; each role has zero or more permissions.
3. Application policies and middleware ask whether the user can perform an ability. For example, `CasePolicy` checks the case permission strings using `$user->can(...)`.
4. If the check fails, the protected action is denied by the Laravel authorization layer.

The application also registers a global `Gate::before` callback: a user with the `Super Admin` role is allowed through Gate checks. This is broader than an ordinary role's permission list, so assign `Super Admin` only to trusted administrators.

Permissions control capabilities, not necessarily which individual records a user may see. Any customer ownership, record scoping, validation, or workflow restrictions applied elsewhere in the controller, policy, middleware, or service still apply. A permission should not be treated as a substitute for those checks.

The React/Livewire interface may hide links or buttons based on access, but hiding a control is not the security boundary. Server-side policies and middleware must authorize every protected action.

## 6. Managing roles and user assignments

The existing User Management screens provide the normal workflow:

1. **Synchronize permissions.** Run `php artisan permissions:sync` (or use the permission synchronization action) so package-defined permissions are present in the database.
2. **Create or configure a role.** Open role management. Create a role and select its permissions, or open an existing role and change its selected permissions. Saving an existing role synchronizes its permission list: permissions not selected are removed from that role.
3. **Assign the role to a user.** Open the user's update screen and select one or more roles. The application stores these as role assignments; permissions are inherited from those roles.
4. **Verify access as that user.** Test both the expected screens and protected actions using a non-Super-Admin account. Also verify that prohibited actions remain denied.

Role management actions are protected by role policies. User creation and updates are protected by user policies. Users who lack the required management permissions cannot use these actions merely by navigating directly to the screen.

## 7. Important implementation notes

- **Role names are not permission bundles.** The seeders create the common role names but do not grant permissions to `Admin`, `Internal User`, `Customer`, or `Engineer`.
- **Super Admin is exceptional.** The global Gate bypass means the permissions displayed on that role are not an effective restriction for its users.
- **Case assignment and closure are not separate registered permissions yet.** `case.assign` and `case.close` exist in `CasePermission`, but are absent from `CaseManagementPermissionProvider`; the current `CasePolicy` also does not define separate abilities for them. The generic `case.update` permission is what the policy checks for updates. Avoid relying on those two enum values until they are registered and their actions enforce them.
- **Permission names preserve their exact casing.** User and role permissions begin with uppercase `User` and `Role`, while most other package permissions are lowercase.
- **Removing every role may not take effect through the current user editor.** The update component only calls `syncRoles` when at least one role is selected. If an account must have all roles removed, verify the assignment actually changed rather than assuming an empty selection cleared it.
- **Role labels do not imply special behavior.** The `User` model also contains an `isExternalUser()` helper that checks for an `External User` role, but `RoleSeeder` does not create that role. The seeded customer-facing role is named `Customer`.

## 8. Suggested role-design practice

- Start with least privilege: grant only the actions needed for each job.
- Use separate roles for materially different responsibilities, rather than granting broad access to everyone.
- Review role assignments periodically, especially `Super Admin`.
- Test with representative accounts after permission changes. A successful page load is not enough; test create, update, delete, and workflow transitions that should be denied as well.
- When adding a new package permission, define it in the package enum and provider, synchronize it, and ensure the corresponding policy or middleware actually checks it. An enum declaration alone does not grant or enforce access.

## Source files

- [RoleSeeder](./database/seeders/RoleSeeder.php)
- [DatabaseSeeder](./database/seeders/DatabaseSeeder.php)
- [UserAdminSeeder](./database/seeders/UserAdminSeeder.php)
- [User model](./app/Models/User.php)
- [Permission registry](./packages/Simplydigital/UserManagement/src/Services/PermissionRegistry.php)
- [Permission synchronizer](./packages/Simplydigital/UserManagement/src/Services/PermissionSynchronizer.php)
- [Permission synchronization command](./packages/Simplydigital/UserManagement/src/Console/SyncPermissionsCommand.php)
- [Case permission provider](./packages/Simplydigital/CaseManagement/src/Authorization/CaseManagementPermissionProvider.php)
- [Case policy](./packages/Simplydigital/CaseManagement/src/Policies/CasePolicy.php)
