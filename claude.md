# Laravel Staff & Client Management System

## Project Overview

This is a comprehensive Laravel-based web application for managing **staff** (cleaning workers), **clients** (house owners), and **tasks** (cleaning assignments). The system includes real-time **chat functionality** allowing staff, clients, and admins to communicate together. Staff members clean client houses based on assigned tasks, and the platform provides a centralized management hub.

## Core Features

### 1. **User Management & Authentication**
- **Role-based authentication**: Admin, Staff, Client
- **2FA (Two-Factor Authentication)**: Two-factor verification setup for enhanced security
- **User profiles**: Each user has profile management capabilities
- **Email-based login**: Uses email for authentication across all roles

### 2. **Staff Management**
- **Staff CRUD Operations**:
  - Create new staff members with profile pictures
  - Edit staff information (name, email, phone, address, role)
  - Delete staff members
  - Store and manage profile pictures on disk (storage/public/staff)
- **Staff Attributes**:
  - FirstName, LastName
  - Email (unique), Username (unique)
  - Role: Temporary, Permanent, Company
  - EmploymentType
  - PhoneNumber, Address
  - ProfilePicture (image upload: jpg, jpeg, png, webp - max 2MB)
  - Password (bcrypt hashed)
- **Staff Table**: Custom table named `Staff` with primary key `StaffID`
- **Staff Routes** (Admin only):
  - `GET /admin/staff/pages-staff-list` - View all staff
  - `GET|POST /admin/staff/pages-staff-add` - Add new staff
  - `GET /admin/staff/pages-staff-edit/{id}` - Edit staff
  - `PUT /admin/staff/pages-staff-edit/{id}` - Update staff
  - `DELETE /admin/staff/pages-staff-delete/{id}` - Delete staff

### 3. **Client Management**
- **Client CRUD Operations**:
  - Manage client companies and their information
  - Support for company registration and details
- **Client Attributes**:
  - company_name, owner_name
  - email, password (bcrypt hashed, hidden in JSON)
  - phone_number
  - company_address
  - tax (boolean - for tax compliance)
  - lockbox
  - company_type
  - file (document storage)
- **Client Table**: Primary key `client_id`
- **Client Houses**: Each client can have multiple houses/properties
- **Client Routes** (Admin & API):
  - `GET /admin/client` - View clients list
  - `GET /admin/client/add-client` - Add client form
  - `GET /api/clients` - Get all clients (API)
  - `GET /api/client/{id}` - Get specific client
  - `POST /api/add-client` - Create client
  - `DELETE /api/client/{id}` - Delete client
  - `PATCH /api/edit-client/{id}` - Update client

### 4. **Client Houses/Properties**
- **Properties per Client**: Each client can manage multiple properties
- **House Attributes**:
  - house_address (address of the property)
  - room (number of rooms)
  - size (property size)
  - time (estimated cleaning time)
  - tools (cleaning tools needed)
  - tasks (specific tasks for that property)
- **Relationship**: Belongs to Client (client_id)

### 5. **Task Management**
- **Task CRUD Operations**: Full create, read, update, delete functionality
- **Task Attributes**:
  - title (task name)
  - description (detailed task description)
  - status (task status tracking)
  - created_at, updated_at (timestamps)
- **Task Relationships**:
  - **Assignees**: Many-to-many relationship with User model via `assigned_users` pivot table
  - **Comments**: One-to-many relationship with Comment model
- **Task Routes** (Authenticated users):
  - `GET /staff` - Staff task dashboard (staff role only)
  - `GET /client` - Client task dashboard (client role only)
  - `GET /tasks` - View all tasks
  - `POST /tasks` - Create new task
  - `GET /tasks/{id}` - View task details
  - `PUT /tasks/{id}` - Update task
  - `DELETE /tasks/{id}` - Delete task

### 6. **Real-Time Chat System**
- **Multi-user Chat**: Staff, clients, and admins can communicate together
- **Chat Features**:
  - Message posting with content validation (max 2000 characters)
  - Emoji reactions on messages (like/react to messages)
  - Private direct messages between users
  - Broadcast messaging using Laravel events
- **Chat Rooms**:
  - **Group Rooms**: Public chat rooms for team communication
  - **Private Rooms**: One-to-one chat rooms (private_room_id format: smaller_id-larger_id)
- **Chat Models**:
  - **Message**: Stores message content, user, room reference
  - **Chatroom**: Stores chat room metadata
  - **Reaction**: Stores message reactions (emoji reactions by users)
  - **Emoji**: Available emoji reactions list
- **Chat Events** (Real-time Broadcasting):
  - `MessagePosted`: Broadcast when new message is sent (excludes sender)
  - `MessageReacted`: Broadcast when user reacts to a message
- **Chat Routes** (Authenticated, requires 2FA):
  - `GET /messages` - Get paginated messages (50 per page) for a room
  - `POST /messages` - Post new message
  - `POST /reactions` - React to a message with emoji
  - `POST /start_chat` - Create or get private chat room
  - `GET /rooms` - Get all available rooms
  - `GET /room/{roomId}` - Get specific room
  - Chat UI: `GET /chat` (via MessageController::chat)
- **Chat Architecture**:
  - Frontend communicates via API endpoints
  - Uses Laravel Broadcasting for real-time updates
  - Emoji system with separate table for emoji storage
  - Reaction model links users, messages, and emojis

### 7. **Comments System**
- **Task Comments**: Users can comment on tasks
- **Comment Files**: Ability to attach files to comments
- **Models**: Comment, CommentFile

### 8. **Dashboard & Analytics**
- **Admin Dashboard**: Analytics dashboard at `/` (requires admin role + 2FA)
- **Admin Overview**: `/admin` route
- **Total Booking Dashboard**: `/dashboard/total-booking`
- **Dashboard Analytics**: Analytics controller for metrics

## Database Schema

### Key Tables
- **users**: Core user table with role column (admin, staff, client)
- **Staff**: Staff member records (custom table name)
- **clients**: Client company information
- **client_houses**: Properties belonging to clients
- **tasks**: Cleaning tasks and assignments
- **assigned_users**: Pivot table for task assignees (many-to-many)
- **messages**: Chat messages
- **chatrooms**: Chat room definitions
- **reactions**: Message emoji reactions
- **emojis**: Available emoji list
- **comments**: Task comments
- **comment_files**: Files attached to comments

### Authentication Tables
- **users** (with role and 2fa_secret columns)
- **password_reset_tokens**
- **sessions**
- **cache** & **cache_locks**

### System Tables
- **jobs** (queue jobs)
- **pulse_tables** (monitoring)
- **telescope_entries** (debugging)

## Key Controllers

### Authentication Controllers
- **AuthenticatedSessionController**: Login/logout
- **RegisteredUserController**: User registration
- **TwoFactorController**: 2FA setup and verification
- **PasswordController, PasswordResetLinkController, etc.**: Password management

### Resource Controllers
- **App\Http\Controllers\API\StaffController**: Staff API operations
- **App\Http\Controllers\API\ClientController**: Client API operations
- **StaffController**: Staff management views
- **ClientController**: Client management views
- **TaskController**: Task CRUD operations
- **MessageController**: Chat and messaging functionality
- **RoomController**: Chat room management
- **TwoFactorController**: 2FA operations

### Other Controllers
- **ProfileController**: User profile management
- **AppController**: Main app controller
- **DashboardControllers**: Analytics and booking dashboards

## Middleware & Security

- **auth**: Requires user authentication
- **2fa**: Requires two-factor authentication verification
- **role:admin**: Admin-only access
- **role:staff**: Staff-only access
- **role:client**: Client-only access
- **verified**: Email verification requirement

## Key File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── API/
│   │   │   ├── ClientController.php
│   │   │   └── StaffController.php
│   │   ├── Auth/
│   │   ├── MessageController.php
│   │   ├── TaskController.php
│   │   ├── ClientController.php
│   │   └── ... other controllers
│   └── Middleware/
├── Models/
│   ├── User.php
│   ├── Staff.php
│   ├── Client.php
│   ├── ClientHouse.php
│   ├── Task.php
│   ├── Message.php
│   ├── Chatroom.php
│   ├── Reaction.php
│   ├── Emoji.php
│   ├── Comment.php
│   ├── CommentFile.php
│   └── AssignedUser.php
├── Events/
│   ├── MessagePosted.php
│   ├── MessageReacted.php
│   └── BotNotification.php
└── ...
routes/
├── web.php (main web routes with middleware groups)
└── api.php (API endpoints)
database/
├── migrations/ (all database schema migrations)
└── factories/ (model factories for testing)
```

## Authentication & Authorization Flow

1. **User Login**: Users authenticate via email/password
2. **Role Assignment**: Users assigned as admin, staff, or client
3. **2FA Setup**: After login, users can setup two-factor authentication
4. **2FA Verification**: Required for accessing protected routes
5. **Role-Based Access**: Routes protected by role middleware

### User Roles
- **Admin**: Full system access, manages staff and clients
- **Staff**: Manage assigned tasks, chat with clients/admin
- **Client**: Manage properties, create tasks, chat with staff/admin

## Development Notes

### Important Patterns
- **Custom Table Names**: Staff model uses custom table `Staff` with primary key `StaffID`
- **Custom Primary Keys**: Client model uses `client_id` instead of `id`
- **Password Hashing**: All passwords (Staff, Client, User) use bcrypt
- **File Storage**: Profile pictures stored in `storage/public/staff/`
- **Broadcasting**: Uses Laravel's broadcasting for real-time chat
- **Transactions**: Staff creation/update uses database transactions for data integrity

### Event Broadcasting
- Uses Laravel Event system for real-time updates
- Events: `MessagePosted`, `MessageReacted`, `BotNotification`
- Broadcasting enabled for authenticated users

### API Endpoints (JSON)
- Staff API: `/api/staff`, `/api/add-staff`, `/api/staff/{id}`
- Client API: `/api/clients`, `/api/client/{id}`, `/api/add-client`
- Messages API: `/api/messages`, `/api/reactions`, `/api/start_chat`

### Validation Rules
- **Email**: Required, valid email format, must be unique
- **Username**: Required, unique
- **Password**: Required, min 6 characters, bcrypt hashed
- **Phone**: Optional, max 20 characters
- **Address**: Optional, max 255 characters
- **Profile Picture**: Optional, jpg/jpeg/png/webp, max 2MB
- **Message Content**: Required, max 2000 characters

## Configuration Files
- `config/app.php`: Application configuration
- `config/broadcasting.php`: Real-time broadcasting setup
- `config/database.php`: Database connection configuration
- `.env`: Environment variables (database, mail, app settings)

## Notes for Development

1. **Cleaning Service Workflow**:
   - Admin creates staff members
   - Admin creates/manages clients and their houses
   - Staff are assigned to tasks (cleaning jobs at client houses)
   - Staff can see assigned tasks in `/staff` dashboard
   - Clients see their tasks in `/client` dashboard
   - All parties can communicate via chat system

2. **Real-Time Features**:
   - Chat messages broadcast to other connected users
   - Message reactions broadcast in real-time
   - Uses Laravel Event broadcasting mechanism

3. **Database Transactions**:
   - Staff creation uses database transactions for consistency
   - Profile pictures are deleted if transaction fails

4. **Security**:
   - All passwords are bcrypt hashed
   - Email verification available
   - Two-factor authentication support
   - CSRF protection
   - Role-based access control
