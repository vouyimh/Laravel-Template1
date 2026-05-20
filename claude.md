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

### 6. **Real-Time Chat System** 💬
- **Multi-user Chat**: Staff, clients, and admins can communicate together
- **Chat Features**:
  - ✅ Text messages with content validation (max 2000 characters)
  - ✅ Emoji reactions on messages (like/react to messages)
  - ✅ Private direct messages between users
  - ✅ Broadcast messaging using Laravel events
  - 🆕 **File Sharing**: Upload and share files in chat (documents, PDFs, etc.)
  - 🆕 **Image Messages**: Send and display images inline
  - 🆕 **Video Messages**: Upload and play videos in chat
  - 🆕 **Voice Messages**: Record and send voice/audio messages
  - 🆕 **WhatsApp-Style Quick Chat**: Open chat window by entering phone number
  - 🆕 **Rich Message Types**: Support for mixed content (text + files)
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
  - `POST /messages` - Post new message (with file upload support)
  - `POST /reactions` - React to a message with emoji
  - `POST /start_chat` - Create or get private chat room
  - `GET /chat-by-phone` - Find/start chat by phone number (WhatsApp-style)
  - `POST /upload-file` - Upload file/image/video/voice message
  - `GET /rooms` - Get all available rooms
  - `GET /room/{roomId}` - Get specific room
  - Chat UI: `GET /chat` (via MessageController::chat)
- **Chat Architecture**:
  - Frontend communicates via API endpoints
  - Uses Laravel Broadcasting for real-time updates
  - Emoji system with separate table for emoji storage
  - Reaction model links users, messages, and emojis

### **Chat Navigation & Visibility Rules** ⚙️ IMPLEMENTED

The chat interface has **two main paths** for communication with **role-based access control**:

#### **Path 1: Middle/Center - Group Chat Rooms**
Group chat rooms are visible and available based on user role. Filtering happens in `MessageController::getVisibleRooms()`:

- **Admin**: ✅ **Can see and access** all group chat rooms
  - Communicate with staff and clients in team channels
- **Staff**: ✅ **Can see and access** all group chat rooms  
  - Communicate with admin and other staff in team channels
- **Client**: ❌ **HIDDEN** - Group chat is completely hidden
  - Returns empty array - No group rooms visible
  - Clients can only use individual/private chats

**Implementation**: `MessageController::chat()` → `getVisibleRooms($user)`
- Returns all group chatrooms (where `private_room_id` is null) for admin/staff
- Returns empty array for clients

#### **Path 2: Left Sidebar - Individual/Private Chat (User List)**
Individual private chats show filtered user lists based on current user's role. Filtering happens in `MessageController::getUserListForChat()`:

- **Admin** 👤:
  - **Can see**: Everyone (all staff + all clients + other admins)
  - **Can initiate chat with**: Anyone
  - **Use case**: Manage team and client communications

- **Staff** 👤:
  - **Can see**: Admins + Other staff
  - **Can initiate chat with**: Admins and other staff
  - **Cannot see**: Clients
  - **Use case**: Direct communication with admin and team for task coordination

- **Client** 👤:
  - **Can see**: ONLY admins (NO staff, NO other clients)
  - **Can initiate chat with**: Admins only
  - **Cannot see**: Staff members or other clients
  - **Use case**: Get support from admin only

**Implementation**: `MessageController::getUserListForChat()`
```php
// Admin: See all users
WHERE id != current_user_id

// Staff: See admins + other staff (NOT clients)
WHERE id != current_user_id AND role IN ('admin', 'staff')

// Client: See ONLY admins (NOT staff, NOT other clients)
WHERE id != current_user_id AND role = 'admin'
```

#### **Chat Visibility Summary Table** 📋

| User Role | Group Chat | Can Chat With | User List Shows |
|-----------|-----------|---------------------------|----------------------|
| **Admin** | ✅ Visible | Anyone (everyone) | All users |
| **Staff** | ✅ Visible | Admins + Other staff | Admins + Staff |
| **Client** | ❌ HIDDEN | ONLY admins | Admins only |

#### **Private Chat Creation & Validation**
Implemented in `MessageController::startChat()` with validation:

1. **User Validation**: Receiver must exist in database
2. **Role-Based Validation**: Check if chat is allowed using `isPrivateChatAllowed()`
   - Admin ↔ Staff/Client ✅
   - Staff ↔ Admin only ✅
   - Client ↔ Clients/Admin (NOT Staff) ✅
3. **Room ID Creation**: `smaller_user_id-larger_user_id` format
4. **Persistence**: Creates/retrieves chatroom with `private_room_id`

**Validation Method**: `MessageController::isPrivateChatAllowed($currentUser, $receiver)`
- Returns `403 Forbidden` if chat not allowed
- Returns `404 Not Found` if receiver doesn't exist

#### **API Endpoints** 🔌

| Endpoint | Method | Purpose | Role Filter |
|----------|--------|---------|------------|
| `/chat-users` | GET | Get filtered user list for private chat | ✅ Role-based |
| `/start_chat` | POST | Create/get private chat room | ✅ Role validation |
| `/messages` | GET | Fetch messages for a room | Backend validates room access |
| `/messages` | POST | Post new message | No role filter (room-level) |
| `/reactions` | POST | React to message | No role filter |

#### **Frontend Implementation Details** 🎨

1. **Room.vue** (Group chat page):
   - Line 20: `const rooms = inject("$rooms");` - Uses filtered rooms from controller
   - Shows only rooms available to current user's role

2. **ListUser.vue** (Online users list):
   - Line 47-49: Maps through `usersOnline` array
   - Currently shows all online users in a room (should be filtered by role on join)
   - Can be enhanced to use `/chat-users` endpoint

3. **startChat Flow**:
   - User clicks on another user in ListUser component
   - `selectReceiver()` function calls `POST /start_chat` with receiver_id
   - Backend validates role-based access
   - If allowed, creates private room; if not, returns 403

#### **Current Status** ✅
- ✅ Group chat filtering implemented in `MessageController::getVisibleRooms()`
- ✅ Private chat user list filtering implemented in `MessageController::getUserListForChat()`
- ✅ Chat validation implemented in `MessageController::isPrivateChatAllowed()`
- ✅ Routes added: `/chat-users` and `/start_chat`
- ⏳ Frontend can optionally call `/chat-users` endpoint for dynamic filtering
- ⏳ Frontend could enhance ListUser component to show role-filtered users in real-time

### 7. **Advanced Chat Features** 🆕

#### **Rich Message Types**
- **Text Messages**: Plain text messages (max 2000 characters)
- **File Messages**: Upload any file type (documents, PDFs, spreadsheets)
- **Image Messages**: Send images inline (JPG, PNG, WebP)
- **Video Messages**: Upload and stream videos (MP4, WebM)
- **Voice Messages**: Record and send audio (MP3, WAV, WebM)
- **Emoji Reactions**: React to any message with emojis

#### **Message Model Extensions**
New columns added to `messages` table:
- `message_type` - Type: 'text', 'file', 'image', 'video', 'voice'
- `file_path` - Path to uploaded file (if applicable)
- `file_name` - Original filename
- `file_size` - File size in bytes
- `mime_type` - MIME type (image/jpeg, video/mp4, etc.)
- `metadata` - JSON field for additional data (duration, dimensions, etc.)

#### **WhatsApp Integration** 📱
Direct link to open WhatsApp chat from private chat window:
- **Feature**: WhatsApp button in private chat header
- **Location**: Private chat header (top right, green button)
- **Icon**: WhatsApp logo (🟢 WhatsApp icon)
- **Behavior**:
  - Click button to open WhatsApp with user's phone number
  - Opens in new tab/window
  - Link format: `https://wa.me/{phone_number}`
  - Only shows if user has phone number in system
- **Use Case**: Quick escalation from in-app chat to WhatsApp for urgent matters

#### **File Upload Endpoints**
- `POST /upload-file` - Upload file/image/video/voice
  - Parameters: `file`, `message_id`, `room_id`
  - Returns: File path, MIME type, file size
  - Validation: File type, size limits
  - Storage: `storage/chat_files/`

#### **Chat Features in Frontend**
Vue components updated to support:
- File picker button (attach files/images/videos)
- Voice recorder button (record audio message)
- Preview thumbnails for images/videos
- Download buttons for files
- Play buttons for audio/video
- File size and duration indicators

### 8. **Comments System**
- **Task Comments**: Users can comment on tasks
- **Comment Files**: Ability to attach files to comments
- **Models**: Comment, CommentFile

### 9. **Dashboard & Analytics**
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
├── Notifications/
│   ├── TaskAssignedNotification.php
│   ├── TaskStatusChangedNotification.php
│   └── NewPrivateMessageNotification.php
└── ...
routes/
├── web.php (main web routes with middleware groups)
├── api.php (API endpoints)
└── channels.php (broadcast channel authorization)
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
- Broadcasting enabled for authenticated users via Pusher

### Notification System 🔔

Real-time in-app notifications persisted in the `notifications` database table and broadcast live via Pusher.

#### Notification Types

| Notification | Trigger | Recipients | Channels |
|---|---|---|---|
| `TaskAssignedNotification` | Task created or new assignees added on update | Each newly assigned user (except the actor) | `database`, `broadcast` |
| `TaskStatusChangedNotification` | Task status changes (pending → in_progress → completed) | All current task assignees (except the actor) | `database`, `broadcast` |
| `NewPrivateMessageNotification` | A private chat message is sent | The other chat participant | `database`, `broadcast` |

#### Notification Bell (Frontend)
- **Component**: `resources/js/components/NotificationBell.vue`
- **Location**: Navbar top-right (present on every page using the main layout)
- **Badge**: Red counter showing unread count, updates in real-time
- **Dropdown**: Lists last 20 notifications with icon, message, timestamp
- **Mark as read**: Click individual notification or "Mark all read" button
- **Real-time**: Listens on `Echo.private('App.Models.User.{id}')` for instant badge updates
- **Icons**: Distinct icons per notification type (task 🔵, status 🟡, message 🟢)

#### Notification Routes

| Endpoint | Method | Purpose |
|---|---|---|
| `/notifications` | GET | Fetch last 20 notifications |
| `/notifications/unread-count` | GET | Get unread badge count |
| `/notifications/mark-all-read` | PATCH | Mark all as read |
| `/notifications/{id}/read` | PATCH | Mark single notification as read |

#### Broadcast Channel
- Laravel's standard user notification channel: `private-App.Models.User.{id}`
- Defined in `routes/channels.php`
- Authorization: user can only listen on their own channel

#### Where Notifications Fire
- `TaskController::store()` → `TaskAssignedNotification` to each assignee
- `TaskController::update()` → `TaskAssignedNotification` to newly added assignees + `TaskStatusChangedNotification` to all assignees when status changes
- `MessageController::store()` → `NewPrivateMessageNotification` to the other user in a private room
- `MessageController::uploadFile()` → `NewPrivateMessageNotification` to the other user in a private room

#### Database
- Table: `notifications` (Laravel standard schema — uuid PK, type, notifiable morph, data JSON, read_at)
- Migration: `2026_05_21_000002_create_notifications_table.php`

### API Endpoints (JSON)
- Staff API: `GET /api/staff`, `POST /api/staff`, `DELETE /api/staff/{id}`
- Client API: `/api/clients`, `/api/client/{id}`, `/api/add-client`, `/api/edit-client/{id}`
- Messages API: `/messages`, `/reactions`, `/start_chat`, `/upload-file`, `/chat-users`
- Notifications API: `/notifications`, `/notifications/unread-count`, `/notifications/mark-all-read`

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

## Deployment Notes - cPanel & WebSocket Limitations ⚠️

### **Real-Time Chat on cPanel**

**❌ Reverb NOT Supported on cPanel**
- Reverb requires Node.js and WebSocket servers
- cPanel does NOT support WebSocket connections (port forwarding issues)
- Reverb cannot be deployed on shared hosting (cPanel)

### **Current Solution: Pusher** ✅

This project is configured to use **Pusher** for real-time messaging:

**Advantages on cPanel:**
- ✅ Cloud-based WebSocket service (external)
- ✅ Works on shared hosting
- ✅ No server configuration needed
- ✅ Reliable and scalable
- ✅ Handles real-time message delivery

**Current Configuration:**
```
BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=2116985
PUSHER_APP_KEY=6a352fa843c4fe1228fe
PUSHER_APP_SECRET=f93af26191431d23d740
PUSHER_APP_CLUSTER=ap1
```

**Frontend Setup** (`resources/js/echo.js`):
```javascript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
```

### **Deployment Steps for cPanel** 🚀

1. **Install Dependencies**:
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   ```

2. **Environment Setup** (`.env`):
   ```
   APP_ENV=production
   APP_DEBUG=false
   BROADCAST_DRIVER=pusher
   BROADCAST_CONNECTION=pusher
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_APP_CLUSTER=ap1
   ```

3. **Database Setup**:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=DatabaseSeeder
   ```

4. **Cache & Config**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Storage Permissions**:
   ```bash
   chmod -R 755 storage
   chmod -R 755 bootstrap/cache
   ```

6. **Assets**:
   - Run `npm run build` before deployment
   - Ensure `public` folder has compiled assets

### **What Works on cPanel** ✅

- ✅ Basic chat message posting (non-real-time)
- ✅ Chat history retrieval
- ✅ Message reactions (via API)
- ✅ Message storage and retrieval
- ✅ User authentication
- ✅ File uploads (staff photos, etc.)

### **What Requires Pusher (Real-Time)** 🔴

- 🔴 Live message delivery (see message instantly)
- 🔴 User online status updates
- 🔴 Typing indicators (if implemented)
- 🔴 Real-time reaction updates
- 🔴 User join/leave notifications

### **Alternative Solutions**

If Pusher is not available or too expensive:

1. **Polling** (Not Recommended):
   - JavaScript polls `/messages` endpoint every 2-3 seconds
   - Poor user experience, high server load
   - Works on cPanel but not ideal

2. **Ably** (Similar to Pusher):
   - Alternative real-time service
   - Update `config/broadcasting.php` to use Ably
   - Adjust `resources/js/echo.js` configuration

3. **Private Server/VPS**:
   - Can run Reverb + Node.js
   - Requires dedicated server (not cPanel shared hosting)
   - Full control over WebSocket server

### **Monitoring Pusher Integration**

Check if Pusher is working:
1. Open browser DevTools → Network tab
2. Look for requests to `wss://ws-ap1.pusher.com`
3. Should see WebSocket connection established
4. Check `window.Echo` object in console:
   ```javascript
   // In browser console:
   console.log(window.Echo)
   // Should show Echo client with pusher broadcaster
   ```

### **Troubleshooting Real-Time Chat**

If real-time chat is not working:

1. **Check Pusher Credentials**:
   - Verify `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_ID`
   - Check `PUSHER_APP_CLUSTER` matches account

2. **Check Broadcasting Config**:
   - Ensure `config/broadcasting.php` has correct Pusher settings
   - Verify `BROADCAST_DRIVER=pusher` in `.env`

3. **Check Frontend**:
   - Run `npm run build` to rebuild JavaScript
   - Check browser console for errors
   - Verify `echo.js` is correctly configured

4. **Check Network**:
   - Ensure firewall allows `wss://` connections
   - Some corporate networks block WebSocket connections

### **Cost Considerations** 💰

**Pusher Pricing** (as of 2026):
- Free tier: Limited messages
- Paid tier: Starts around $10-50/month depending on usage
- Consider message volume for your application

**Alternative**: Use polling with database as fallback (works on cPanel, slower)
