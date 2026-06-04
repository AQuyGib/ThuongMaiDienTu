<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Initial data load for Communication Hub.
     * Auto-seeds standard rooms if missing.
     */
    public function init()
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role_id, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Auto-run migrations if tables don't exist
        if (!\Illuminate\Support\Facades\Schema::hasTable('chat_rooms') || 
            !\Illuminate\Support\Facades\Schema::hasTable('chat_room_members') || 
            !\Illuminate\Support\Facades\Schema::hasTable('chat_messages')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                // Silent catch or fallback
            }
        }

        // Auto-seed default rooms
        $defaultRooms = [
            [
                'room_id' => 'staff',
                'name' => '💬 Kênh Nhân viên',
                'description' => 'Kênh thảo luận công việc chung cho toàn thể nhân sự',
                'type' => 'group',
                'pinned_message' => 'Chào mừng mọi người đến với kênh chat chung của Điện Máy Pro! Hãy giữ lịch sự và vui vẻ khi trao đổi.'
            ],
            [
                'room_id' => 'announcement',
                'name' => '📢 Thông báo & Tin tức',
                'description' => 'Thông báo và tin tức chính thức từ Ban quản lý',
                'type' => 'announcement',
                'pinned_message' => 'Lịch bảo trì hệ thống định kỳ vào 23:00 tối Chủ Nhật tuần này. Vui lòng hoàn thành mọi giao dịch trước đó.'
            ],
            [
                'room_id' => 'executive',
                'name' => '🔒 Phòng Ban Quản lý & Admin',
                'description' => 'Phòng chat nội bộ chỉ dành riêng cho Admin và Manager',
                'type' => 'private',
                'pinned_message' => 'Báo cáo doanh thu tối mật hàng tuần. Không chia sẻ tài liệu ra ngoài.'
            ]
        ];

        foreach ($defaultRooms as $r) {
            $room = ChatRoom::firstOrCreate(['room_id' => $r['room_id']], $r);
            if ($room->wasRecentlyCreated) {
                if ($r['room_id'] === 'executive') {
                    $users = User::whereIn('role_id', [1, 2])->get();
                } else {
                    $users = User::where('role_id', '!=', 3)->get();
                }
                foreach ($users as $u) {
                    ChatRoomMember::firstOrCreate([
                        'room_id' => $r['room_id'],
                        'user_id' => $u->user_id
                    ], [
                        'room_role' => $u->role_id == 1 ? 'leader' : ($u->role_id == 2 ? 'co-leader' : 'member')
                    ]);
                }
            }
        }

        // Auto-seed mock messages if rooms are empty
        foreach (ChatRoom::all() as $room) {
            if ($room->messages()->count() === 0) {
                if ($room->room_id === 'staff') {
                    $nam = User::where('email', 'technical.nam@dienmaypro.com.vn')->first();
                    $hung = User::where('email', 'technical.hung@dienmaypro.com.vn')->first();
                    
                    ChatMessage::create([
                        'message_id' => 's1',
                        'room_id' => 'staff',
                        'sender_id' => $nam ? $nam->user_id : 3,
                        'sender_name' => $nam ? $nam->full_name : 'Trần Kỹ Thuật Nam',
                        'sender_role' => 'Kỹ thuật viên',
                        'avatar_color' => 'bg-emerald-600',
                        'content' => 'Chào buổi sáng cả nhà! Hôm nay tôi trực kỹ thuật hỗ trợ POS nhé.',
                        'is_read' => true,
                        'reactions' => json_encode([['emoji' => '👍', 'users' => [$hung ? $hung->full_name : 'Phạm Kỹ Thuật Hùng']]])
                    ]);
                    ChatMessage::create([
                        'message_id' => 's2',
                        'room_id' => 'staff',
                        'sender_id' => $hung ? $hung->user_id : 4,
                        'sender_name' => $hung ? $hung->full_name : 'Phạm Kỹ Thuật Hùng',
                        'sender_role' => 'Kỹ thuật viên',
                        'avatar_color' => 'bg-amber-600',
                        'content' => 'Ok anh Nam, tí có ca sửa chữa máy giặt em ới nhé.',
                        'is_read' => true,
                        'reactions' => json_encode([])
                    ]);
                } else if ($room->room_id === 'announcement') {
                    $admin = User::where('role_id', 1)->first();
                    $manager = User::where('role_id', 2)->first();
                    ChatMessage::create([
                        'message_id' => 'a1',
                        'room_id' => 'announcement',
                        'sender_id' => $admin ? $admin->user_id : 1,
                        'sender_name' => $admin ? $admin->full_name : 'Quản Trị Viên',
                        'sender_role' => 'Admin',
                        'avatar_color' => 'bg-indigo-600',
                        'content' => 'Đã hoàn thành nâng cấp tính năng xuất báo cáo nhân sự Excel & PDF cực đẹp!',
                        'is_read' => true,
                        'reactions' => json_encode([['emoji' => '🔥', 'users' => [$manager ? $manager->full_name : 'Nguyễn Quản Lý']]])
                    ]);
                } else if ($room->room_id === 'executive') {
                    $admin = User::where('role_id', 1)->first();
                    $manager = User::where('role_id', 2)->first();
                    ChatMessage::create([
                        'message_id' => 'e1',
                        'room_id' => 'executive',
                        'sender_id' => $manager ? $manager->user_id : 2,
                        'sender_name' => $manager ? $manager->full_name : 'Nguyễn Quản Lý',
                        'sender_role' => 'Manager',
                        'avatar_color' => 'bg-purple-600',
                        'content' => 'Báo cáo doanh thu tháng này của chúng ta đang tăng trưởng 12% so với tháng trước.',
                        'is_read' => true,
                        'reactions' => json_encode([['emoji' => '❤️', 'users' => [$admin ? $admin->full_name : 'Quản Trị Viên']]])
                    ]);
                }
            }
        }

        // Fetch rooms with relations
        $roomsData = ChatRoom::with(['users' => function($q) {
            $q->with('role');
        }])->get()->map(function ($room) {
            return [
                'id' => $room->room_id,
                'name' => $room->name,
                'description' => $room->description,
                'type' => $room->type,
                'pinnedMessage' => $room->pinned_message,
                'unreadCount' => 0,
                'members' => $room->users->map(function ($u) {
                    return [
                        'id' => $u->user_id,
                        'name' => $u->full_name,
                        'role' => $u->role ? $u->role->name : 'Staff',
                        'status' => $u->isOnline() ? 'online' : 'offline',
                        'avatarColor' => $u->role_id == 1 ? 'bg-indigo-600' : ($u->role_id == 2 ? 'bg-purple-600' : ($u->role_id == 4 ? 'bg-emerald-600' : 'bg-slate-600')),
                        'roomRole' => $u->pivot->room_role
                    ];
                })
            ];
        });

        // Fetch messages group by room_id
        $messagesData = [];
        foreach (ChatRoom::all() as $room) {
            $messagesData[$room->room_id] = $room->messages->map(function ($msg) use ($currentUser) {
                $reactions = is_string($msg->reactions) ? json_decode($msg->reactions, true) : $msg->reactions;
                return [
                    'id' => $msg->message_id,
                    'sender' => $msg->sender_name,
                    'senderRole' => $msg->sender_role,
                    'avatarColor' => $msg->avatar_color,
                    'content' => $msg->content ?? '',
                    'time' => $msg->created_at ? $msg->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i A') : '08:00 AM',
                    'isMe' => $msg->sender_id === $currentUser->user_id,
                    'replyTo' => $msg->reply_to_sender ? [
                        'sender' => $msg->reply_to_sender,
                        'content' => $msg->reply_to_content
                    ] : null,
                    'reactions' => $reactions ?? [],
                    'attachment' => $msg->attachment_name ? [
                        'name' => $msg->attachment_name,
                        'type' => $msg->attachment_type,
                        'url' => $msg->attachment_url,
                        'size' => $msg->attachment_size
                    ] : null,
                    'isRead' => $msg->is_read
                ];
            });
        }

        // Fetch all active employees (role_id != 3) to allow adding to rooms
        $allEmployees = User::with('role')->where('role_id', '!=', 3)->where('status', 'Active')->get()->map(function ($u) {
            return [
                'id' => $u->user_id,
                'name' => $u->full_name,
                'role' => $u->role ? $u->role->name : 'Staff',
                'status' => $u->isOnline() ? 'online' : 'offline',
                'avatarColor' => $u->role_id == 1 ? 'bg-indigo-600' : ($u->role_id == 2 ? 'bg-purple-600' : ($u->role_id == 4 ? 'bg-emerald-600' : 'bg-slate-600'))
            ];
        });

        return response()->json([
            'rooms' => $roomsData,
            'messages' => $messagesData,
            'all_employees' => $allEmployees
        ]);
    }

    /**
     * Create custom room
     */
    public function createRoom(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role_id, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:250',
            'type' => 'required|in:group,private'
        ]);

        $roomId = 'custom-' . time();
        $room = ChatRoom::create([
            'room_id' => $roomId,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type
        ]);

        // Add creator as leader
        ChatRoomMember::create([
            'room_id' => $roomId,
            'user_id' => $currentUser->user_id,
            'room_role' => 'leader'
        ]);

        return response()->json([
            'id' => $room->room_id,
            'name' => $room->name,
            'description' => $room->description,
            'type' => $room->type,
            'unreadCount' => 0,
            'members' => [[
                'id' => $currentUser->user_id,
                'name' => $currentUser->full_name,
                'role' => $currentUser->role ? $currentUser->role->name : 'Staff',
                'status' => 'online',
                'avatarColor' => $currentUser->role_id == 1 ? 'bg-indigo-600' : 'bg-purple-600',
                'roomRole' => 'leader'
            ]]
        ]);
    }

    /**
     * Delete room
     */
    public function deleteRoom($room_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role_id, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (in_array($room_id, ['staff', 'announcement'])) {
            return response()->json(['error' => 'Cannot delete system rooms'], 400);
        }

        $room = ChatRoom::findOrFail($room_id);
        
        // Verify user is leader or global admin
        $member = ChatRoomMember::where('room_id', $room_id)->where('user_id', $currentUser->user_id)->first();
        if ($currentUser->role_id !== 1 && (!$member || $member->room_role !== 'leader')) {
            return response()->json(['error' => 'Only room leader or admin can delete room'], 403);
        }

        $room->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Add member to room
     */
    public function addMember(Request $request, $room_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role_id, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id'
        ]);

        $room = ChatRoom::findOrFail($room_id);
        
        // Check permissions: creator/leader/co-leader or global admin
        $member = ChatRoomMember::where('room_id', $room_id)->where('user_id', $currentUser->user_id)->first();
        if ($currentUser->role_id !== 1 && (!$member || !in_array($member->room_role, ['leader', 'co-leader']))) {
            return response()->json(['error' => 'Only leaders or managers can add members'], 403);
        }

        $newMember = ChatRoomMember::firstOrCreate([
            'room_id' => $room_id,
            'user_id' => $request->user_id
        ], [
            'room_role' => 'member'
        ]);

        $user = User::with('role')->find($request->user_id);
        
        // System message notice
        $timeStr = now()->timezone('Asia/Ho_Chi_Minh')->format('H:i A');
        $sysMsg = ChatMessage::create([
            'message_id' => 'sys-' . time() . '-' . rand(100,999),
            'room_id' => $room_id,
            'sender_id' => $currentUser->user_id,
            'sender_name' => 'Hệ thống',
            'sender_role' => 'System',
            'avatar_color' => 'bg-slate-500',
            'content' => "📢 {$currentUser->full_name} đã thêm {$user->full_name} vào phòng chat.",
            'is_read' => true,
            'reactions' => json_encode([])
        ]);

        return response()->json([
            'member' => [
                'id' => $user->user_id,
                'name' => $user->full_name,
                'role' => $user->role ? $user->role->name : 'Staff',
                'status' => $user->isOnline() ? 'online' : 'offline',
                'avatarColor' => $user->role_id == 1 ? 'bg-indigo-600' : ($user->role_id == 2 ? 'bg-purple-600' : 'bg-emerald-600'),
                'roomRole' => 'member'
            ],
            'system_message' => [
                'id' => $sysMsg->message_id,
                'sender' => $sysMsg->sender_name,
                'senderRole' => $sysMsg->sender_role,
                'avatarColor' => $sysMsg->avatar_color,
                'content' => $sysMsg->content,
                'time' => $timeStr,
                'isMe' => false,
                'reactions' => [],
                'isRead' => true
            ]
        ]);
    }

    /**
     * Remove member from room
     */
    public function removeMember(Request $request, $room_id, $user_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role_id, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($currentUser->user_id == $user_id) {
            return response()->json(['error' => 'Cannot remove yourself'], 400);
        }

        // Check permission
        $me = ChatRoomMember::where('room_id', $room_id)->where('user_id', $currentUser->user_id)->first();
        $target = ChatRoomMember::where('room_id', $room_id)->where('user_id', $user_id)->firstOrFail();

        if ($currentUser->role_id !== 1) {
            if (!$me) return response()->json(['error' => 'You are not in this room'], 403);
            if ($me->room_role === 'member') return response()->json(['error' => 'Unauthorized'], 403);
            if ($me->room_role === 'co-leader' && in_array($target->room_role, ['leader', 'co-leader'])) {
                return response()->json(['error' => 'Co-leaders cannot remove leaders or other co-leaders'], 403);
            }
        }

        $user = User::find($user_id);
        $target->delete();

        // System message notice
        $timeStr = now()->timezone('Asia/Ho_Chi_Minh')->format('H:i A');
        $sysMsg = ChatMessage::create([
            'message_id' => 'sys-' . time() . '-' . rand(100,999),
            'room_id' => $room_id,
            'sender_id' => $currentUser->user_id,
            'sender_name' => 'Hệ thống',
            'sender_role' => 'System',
            'avatar_color' => 'bg-slate-500',
            'content' => "📢 {$currentUser->full_name} đã mời {$user->full_name} rời khỏi phòng chat.",
            'is_read' => true,
            'reactions' => json_encode([])
        ]);

        return response()->json([
            'success' => true,
            'system_message' => [
                'id' => $sysMsg->message_id,
                'sender' => $sysMsg->sender_name,
                'senderRole' => $sysMsg->sender_role,
                'avatarColor' => $sysMsg->avatar_color,
                'content' => $sysMsg->content,
                'time' => $timeStr,
                'isMe' => false,
                'reactions' => [],
                'isRead' => true
            ]
        ]);
    }

    /**
     * Update member room role
     */
    public function updateRole(Request $request, $room_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role_id, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:leader,co-leader,member'
        ]);

        // Check permission
        $me = ChatRoomMember::where('room_id', $room_id)->where('user_id', $currentUser->user_id)->first();
        if ($currentUser->role_id !== 1 && (!$me || $me->room_role !== 'leader')) {
            return response()->json(['error' => 'Only room leaders or admins can update roles'], 403);
        }

        $target = ChatRoomMember::where('room_id', $room_id)->where('user_id', $request->user_id)->firstOrFail();
        $target->update(['room_role' => $request->role]);

        $user = User::find($request->user_id);
        $roleLabels = [
            'leader' => 'Trưởng nhóm',
            'co-leader' => 'Phó nhóm',
            'member' => 'Thành viên'
        ];

        // System message notice
        $timeStr = now()->timezone('Asia/Ho_Chi_Minh')->format('H:i A');
        $sysMsg = ChatMessage::create([
            'message_id' => 'sys-' . time() . '-' . rand(100,999),
            'room_id' => $room_id,
            'sender_id' => $currentUser->user_id,
            'sender_name' => 'Hệ thống',
            'sender_role' => 'System',
            'avatar_color' => 'bg-slate-500',
            'content' => "📢 {$currentUser->full_name} đã bổ nhiệm {$user->full_name} làm {$roleLabels[$request->role]} của phòng chat.",
            'is_read' => true,
            'reactions' => json_encode([])
        ]);

        return response()->json([
            'success' => true,
            'system_message' => [
                'id' => $sysMsg->message_id,
                'sender' => $sysMsg->sender_name,
                'senderRole' => $sysMsg->sender_role,
                'avatarColor' => $sysMsg->avatar_color,
                'content' => $sysMsg->content,
                'time' => $timeStr,
                'isMe' => false,
                'reactions' => [],
                'isRead' => true
            ]
        ]);
    }

    /**
     * Send new message
     */
    public function sendMessage(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role_id, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'room_id' => 'required|exists:chat_rooms,room_id',
            'content' => 'nullable|string',
            'reply_to_sender' => 'nullable|string',
            'reply_to_content' => 'nullable|string',
            'file' => 'nullable|file|max:10240' // max 10MB
        ]);

        if (empty($request->content) && !$request->hasFile('file')) {
            return response()->json(['error' => 'Empty message'], 400);
        }

        $attachmentName = null;
        $attachmentType = null;
        $attachmentUrl = null;
        $attachmentSize = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('chat_attachments', 'public');
            $attachmentUrl = asset('storage/' . $path);
            $attachmentName = $file->getClientOriginalName();
            $attachmentType = str_contains($file->getMimeType(), 'image/') ? 'image' : 'file';
            $attachmentSize = round($file->getSize() / 1024, 1) . ' KB';
        }

        $messageId = 'm-' . time() . '-' . rand(100,999);
        $timeStr = now()->timezone('Asia/Ho_Chi_Minh')->format('H:i A');

        $roleName = $currentUser->role ? $currentUser->role->name : 'Staff';
        $avatarColor = $currentUser->role_id == 1 ? 'bg-indigo-600' : 'bg-purple-600';

        $msg = ChatMessage::create([
            'message_id' => $messageId,
            'room_id' => $request->room_id,
            'sender_id' => $currentUser->user_id,
            'sender_name' => $currentUser->full_name,
            'sender_role' => $roleName,
            'avatar_color' => $avatarColor,
            'content' => $request->content,
            'reply_to_sender' => $request->reply_to_sender,
            'reply_to_content' => $request->reply_to_content,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'attachment_url' => $attachmentUrl,
            'attachment_size' => $attachmentSize,
            'is_read' => true,
            'reactions' => json_encode([])
        ]);

        return response()->json([
            'message' => [
                'id' => $msg->message_id,
                'sender' => $msg->sender_name,
                'senderRole' => $msg->sender_role,
                'avatarColor' => $msg->avatar_color,
                'content' => $msg->content ?? '',
                'time' => $timeStr,
                'isMe' => true,
                'replyTo' => $msg->reply_to_sender ? [
                    'sender' => $msg->reply_to_sender,
                    'content' => $msg->reply_to_content
                ] : null,
                'reactions' => [],
                'attachment' => $msg->attachment_name ? [
                    'name' => $msg->attachment_name,
                    'type' => $msg->attachment_type,
                    'url' => $msg->attachment_url,
                    'size' => $msg->attachment_size
                ] : null,
                'isRead' => true
            ]
        ]);
    }

    /**
     * Add/toggle message emoji reaction
     */
    public function toggleReaction(Request $request, $message_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role_id, [1, 2])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'emoji' => 'required|string|max:10'
        ]);

        $msg = ChatMessage::findOrFail($message_id);
        $reactions = is_string($msg->reactions) ? json_decode($msg->reactions, true) : ($msg->reactions ?? []);
        
        $emoji = $request->emoji;
        $userName = $currentUser->full_name;

        $foundEmojiIndex = -1;
        foreach ($reactions as $idx => $r) {
            if ($r['emoji'] === $emoji) {
                $foundEmojiIndex = $idx;
                break;
            }
        }

        if ($foundEmojiIndex > -1) {
            $r = $reactions[$foundEmojiIndex];
            if (in_array($userName, $r['users'])) {
                // Remove user
                $r['users'] = array_values(array_filter($r['users'], fn($u) => $u !== $userName));
                if (empty($r['users'])) {
                    array_splice($reactions, $foundEmojiIndex, 1);
                } else {
                    $reactions[$foundEmojiIndex] = $r;
                }
            } else {
                // Add user
                $reactions[$foundEmojiIndex]['users'][] = $userName;
            }
        } else {
            // Create new reaction group
            $reactions[] = [
                'emoji' => $emoji,
                'users' => [$userName]
            ];
        }

        $msg->update(['reactions' => json_encode($reactions)]);

        return response()->json([
            'success' => true,
            'reactions' => $reactions
        ]);
    }
}
