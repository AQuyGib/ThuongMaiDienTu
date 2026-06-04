import React, { useState, useEffect, useRef } from 'react';
import { 
  X, 
  Send, 
  MessageSquare, 
  Search, 
  Users, 
  Lock, 
  Volume2, 
  VolumeX, 
  Smile, 
  Paperclip, 
  Pin, 
  Reply, 
  Sparkles, 
  FileText, 
  Image as ImageIcon,
  CheckCheck,
  ChevronRight,
  UserCheck,
  Plus,
  Trash2,
  ArrowLeft
} from 'lucide-react';
import { isEn, t } from '../helpers';
import axios from 'axios';

// --- CÁC ĐỊNH NGHĨA KHOÁ/ĐỐI TƯỢNG (INTERFACES) ---
interface Member {
  id: number;
  name: string;
  role: string;
  status: 'online' | 'offline';
  avatarColor: string;
  roomRole?: 'leader' | 'co-leader' | 'member';
}

interface Reaction {
  emoji: string;
  users: string[];
}

interface Attachment {
  name: string;
  type: 'image' | 'file';
  url?: string;
  size?: string;
}

interface Message {
  id: string;
  sender: string;
  senderRole: string;
  avatarColor: string;
  content: string;
  time: string;
  isMe: boolean;
  replyTo?: {
    sender: string;
    content: string;
  };
  reactions: Reaction[];
  attachment?: Attachment;
  isRead: boolean;
}

interface ChatRoom {
  id: string;
  name: string;
  description: string;
  type: 'group' | 'announcement' | 'private' | 'ai';
  pinnedMessage?: string;
  members: Member[];
  unreadCount: number;
}

interface CommunicationHubProps {
  isOpen: boolean;
  onClose: () => void;
  onUnreadChange?: (count: number) => void;
  userRoleId: number;
}

interface CustomDialog {
  isOpen: boolean;
  type: 'alert' | 'confirm';
  title: string;
  message: string;
  onConfirm?: () => void;
  onCancel?: () => void;
}


export default function CommunicationHub({ isOpen, onClose, onUnreadChange, userRoleId }: CommunicationHubProps) {
  // Kiểm tra bảo vệ: chỉ cho phép quản trị viên (1) và quản lý (2) truy cập/sử dụng
  if (userRoleId !== 1 && userRoleId !== 2) {
    return null;
  }

  // Xác định động các thuộc tính người dùng hiện tại dựa vào userRoleId
  const currentUserId = userRoleId === 1 ? 1 : 2;
  const cleanCurrentUserName = userRoleId === 1 ? 'Nguyễn Văn An' : 'Trần Thị Bình';
  const currentUserName = userRoleId === 1 ? 'Nguyễn Văn An (Bạn)' : 'Trần Thị Bình (Bạn)';
  const currentUserRoleName = userRoleId === 1 ? 'Admin' : 'Manager';
  const currentUserAvatarColor = userRoleId === 1 ? 'bg-indigo-600' : 'bg-purple-600';

  // Hàm hỗ trợ kiểm tra xem tin nhắn có phải của người dùng hiện tại hay không
  const isMessageFromMe = (msg: Message) => {
    const cleanSender = msg.sender.replace(' (Bạn)', '').replace(' (You)', '');
    return cleanSender === cleanCurrentUserName;
  };

  // Hàm hỗ trợ kiểm tra xem người dùng hiện tại có đủ quyền hạn quản lý thành viên này trong phòng hay không
  const canManageMember = (targetMember: Member, currentRoomMembers: Member[]) => {
    if (targetMember.id === currentUserId) return false;
    if (userRoleId === 1) return true; // Quản trị viên (Admin) cấp cao nhất có quyền quản lý tất cả mọi người
    
    const currentUserInRoom = currentRoomMembers.find(m => m.id === currentUserId);
    const currentUserRoomRole = currentUserInRoom?.roomRole || 'member';
    
    if (currentUserRoomRole === 'leader') return true;
    if (currentUserRoomRole === 'co-leader') {
      return targetMember.roomRole !== 'leader' && targetMember.roomRole !== 'co-leader';
    }
    return false;
  };

  // Trạng thái hiển thị thông báo hộp thoại tự thiết kế (alert/confirm)
  const [dialog, setDialog] = useState<CustomDialog | null>(null);

  // Trạng thái quản lý giao diện trên điện thoại ('rooms' hiển thị danh sách phòng, 'chat' hiển thị nội dung chat)
  const [mobileView, setMobileView] = useState<'rooms' | 'chat'>('rooms');

  // Xác định động kích thước màn hình để tránh lỗi layout css trên các thiết bị khác nhau
  const [isDesktop, setIsDesktop] = useState(typeof window !== 'undefined' ? window.innerWidth >= 768 : true);

  useEffect(() => {
    const handleResize = () => {
      setIsDesktop(typeof window !== 'undefined' ? window.innerWidth >= 768 : true);
    };
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const showCustomAlert = (title: string, message: string) => {
    setDialog({
      isOpen: true,
      type: 'alert',
      title,
      message,
      onConfirm: () => setDialog(null)
    });
  };

  const showCustomConfirm = (title: string, message: string, onConfirmAction: () => void) => {
    setDialog({
      isOpen: true,
      type: 'confirm',
      title,
      message,
      onConfirm: () => {
        onConfirmAction();
        setDialog(null);
      },
      onCancel: () => setDialog(null)
    });
  };

  // Trạng thái bật/tắt âm thanh thông báo
  const [soundEnabled, setSoundEnabled] = useState(true);

  // Tìm kiếm phòng chat / nội dung tin nhắn
  const [channelSearch, setChannelSearch] = useState('');
  const [messageSearch, setMessageSearch] = useState('');
  const [isMsgSearchOpen, setIsMsgSearchOpen] = useState(false);

  // Trạng thái bật/tắt hiển thị các cột bổ trợ (danh sách thành viên, ghim tin nhắn)
  const [showMembers, setShowMembers] = useState(false);
  const [isPinnedCollapsed, setIsPinnedCollapsed] = useState(false);

  // Trạng thái phòng chat đang hoạt động và soạn thảo tin nhắn
  const [activeRoomId, setActiveRoomId] = useState('staff');
  const [inputText, setInputText] = useState('');
  const [quotedMessage, setQuotedMessage] = useState<Message | null>(null);
  const [selectedFile, setSelectedFile] = useState<Attachment | null>(null);
  const [rawFile, setRawFile] = useState<File | null>(null);
  const [typingUser, setTypingUser] = useState<string | null>(null);

  // Các trạng thái của form thêm phòng chat mới
  const [isAddRoomOpen, setIsAddRoomOpen] = useState(false);
  const [newRoomName, setNewRoomName] = useState('');
  const [newRoomDesc, setNewRoomDesc] = useState('');
  const [newRoomType, setNewRoomType] = useState<'group' | 'private'>('group');

  // Trạng thái mở form thêm thành viên vào phòng chat
  const [isAddingMember, setIsAddingMember] = useState(false);

  // ID thành viên đang mở menu lựa chọn thay đổi chức vụ hoặc xóa thành viên
  const [activeMenuMemberId, setActiveMenuMemberId] = useState<number | null>(null);

  // Danh sách toàn bộ nhân viên có sẵn trong hệ thống để thêm vào phòng
  const [allEmployees, setAllEmployees] = useState<Member[]>([]);
  const [loading, setLoading] = useState(false);

  // Tự động tải dữ liệu chat thực tế từ máy chủ khi component được mở
  useEffect(() => {
    if (isOpen) {
      const loadChatData = async () => {
        setLoading(true);
        try {
          const response = await axios.get('/admin/chat/init');
          setRooms(response.data.rooms);
          setMessages(response.data.messages);
          setAllEmployees(response.data.all_employees);
        } catch (error) {
          console.error("Failed to load chat data", error);
          showCustomAlert(t("Lỗi tải dữ liệu", "Load Error"), t("Không thể kết nối với máy chủ chat.", "Cannot connect to chat server."));
        } finally {
          setLoading(false);
        }
      };
      loadChatData();
    }
  }, [isOpen]);

  // Tự động đóng menu thao tác thành viên khi click ra ngoài
  useEffect(() => {
    const handleOutsideClick = (e: MouseEvent) => {
      if (activeMenuMemberId !== null) {
        const target = e.target as HTMLElement;
        if (!target.closest('.group\\/member')) {
          setActiveMenuMemberId(null);
        }
      }
    };
    document.addEventListener('mousedown', handleOutsideClick);
    return () => document.removeEventListener('mousedown', handleOutsideClick);
  }, [activeMenuMemberId]);

  // Các tham chiếu useRef
  const messageEndRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // --- TRẠNG THÁI DANH SÁCH PHÒNG CHAT & TIN NHẮN TỪ DATABASE ---
  const [rooms, setRooms] = useState<ChatRoom[]>(() => [
    {
      id: 'staff',
      name: t('💬 Kênh Nhân viên', '💬 Staff Lounge'),
      description: t('Kênh thảo luận công việc chung cho toàn thể nhân sự', 'General work discussion for all staff members'),
      type: 'group',
      members: [],
      unreadCount: 0
    },
    {
      id: 'announcement',
      name: t('📢 Thông báo & Tin tức', '📢 News & Alerts'),
      description: t('Thông báo và tin tức chính thức từ Ban quản lý', 'Official broadcasts and notices from Management'),
      type: 'announcement',
      members: [],
      unreadCount: 0
    },
    {
      id: 'executive',
      name: t('🔒 Phòng Ban Quản lý & Admin', '🔒 Executive Suite'),
      description: t('Phòng chat nội bộ chỉ dành riêng cho Admin và Manager', 'Restricted room for Admin and Managers only'),
      type: 'private',
      members: [],
      unreadCount: 0
    }
  ]);

  const [messages, setMessages] = useState<Record<string, Message[]>>(() => ({
    staff: [],
    announcement: [],
    executive: []
  }));

  // --- HỆ THỐNG PHÁT ÂM THANH THÔNG BÁO BẰNG AUDIO CONTEXT SYNTH ---
  const playChime = (type: 'send' | 'receive') => {
    if (!soundEnabled) return;
    try {
      const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
      if (!AudioContextClass) return;
      const ctx = new AudioContextClass();
      
      const osc1 = ctx.createOscillator();
      const gain1 = ctx.createGain();
      osc1.connect(gain1);
      gain1.connect(ctx.destination);
      
      const osc2 = ctx.createOscillator();
      const gain2 = ctx.createGain();
      osc2.connect(gain2);
      gain2.connect(ctx.destination);
      
      const now = ctx.currentTime;
      
      if (type === 'send') {
        osc1.frequency.setValueAtTime(587.33, now); // D5
        osc1.frequency.exponentialRampToValueAtTime(880, now + 0.12); // A5
        osc2.frequency.setValueAtTime(1174.66, now); // D6
        
        gain1.gain.setValueAtTime(0.08, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
        gain2.gain.setValueAtTime(0.04, now);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
        
        osc1.start(now);
        osc1.stop(now + 0.2);
        osc2.start(now);
        osc2.stop(now + 0.2);
      } else { // receive
        osc1.frequency.setValueAtTime(880, now); // A5
        osc1.frequency.exponentialRampToValueAtTime(587.33, now + 0.15); // D5
        osc2.frequency.setValueAtTime(783.99, now); // G5
        
        gain1.gain.setValueAtTime(0.1, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
        gain2.gain.setValueAtTime(0.05, now);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
        
        osc1.start(now);
        osc1.stop(now + 0.3);
        osc2.start(now);
        osc2.stop(now + 0.3);
      }
    } catch (e) {
      console.error('Failed to play chime', e);
    }
  };

  // --- ĐỒNG BỘ HÓA TỔNG SỐ TIN NHẮN CHƯA ĐỌC ---
  useEffect(() => {
    const totalUnread = rooms.reduce((sum, r) => sum + r.unreadCount, 0);
    if (onUnreadChange) {
      onUnreadChange(totalUnread);
    }
  }, [rooms, onUnreadChange]);

  // Tự động cuộn xuống cuối cùng khi đổi phòng hoặc có tin nhắn mới
  useEffect(() => {
    messageEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages, activeRoomId, typingUser]);

  // Xóa bộ đếm chưa đọc khi người dùng click vào xem phòng
  useEffect(() => {
    if (isOpen) {
      setRooms(prev => prev.map(r => r.id === activeRoomId ? { ...r, unreadCount: 0 } : r));
    }
  }, [activeRoomId, isOpen]);

  // --- XỬ LÝ GỬI TIN NHẮN ---
  const handleSendMessage = async () => {
    if (!inputText.trim() && !rawFile) return;

    const currentRoom = rooms.find(r => r.id === activeRoomId);
    if (currentRoom?.type === 'announcement' && activeRoomId !== 'announcement') {
      return;
    }

    try {
      const formData = new FormData();
      formData.append('room_id', activeRoomId);
      if (inputText.trim()) {
        formData.append('content', inputText);
      }
      if (quotedMessage) {
        formData.append('reply_to_sender', quotedMessage.sender);
        formData.append('reply_to_content', quotedMessage.content);
      }
      if (rawFile) {
        formData.append('file', rawFile);
      }

      const response = await axios.post('/admin/chat/messages', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      const savedMsg = response.data.message;
      setMessages(prev => ({
        ...prev,
        [activeRoomId]: [...(prev[activeRoomId] || []), savedMsg]
      }));

      setInputText('');
      setQuotedMessage(null);
      setSelectedFile(null);
      setRawFile(null);
      playChime('send');
    } catch (error) {
      console.error("Failed to send message", error);
      showCustomAlert(t("Lỗi gửi tin nhắn", "Send Error"), t("Không thể gửi tin nhắn đến máy chủ.", "Could not send message to server."));
    }
  };

  // --- XỬ LÝ TẠO PHÒNG CHAT MỚI ---
  const handleAddRoom = async () => {
    if (!newRoomName.trim()) {
      showCustomAlert(
        t("Thông tin không hợp lệ", "Invalid Info"),
        t("Vui lòng nhập tên phòng!", "Please enter room name!")
      );
      return;
    }

    try {
      const response = await axios.post('/admin/chat/rooms', {
        name: newRoomName.trim(),
        description: newRoomDesc.trim(),
        type: newRoomType
      });

      const newRoom = response.data;
      setRooms(prev => [...prev, newRoom]);
      setMessages(prev => ({
        ...prev,
        [newRoom.id]: []
      }));
      setActiveRoomId(newRoom.id);

      setNewRoomName('');
      setNewRoomDesc('');
      setNewRoomType('group');
      setIsAddRoomOpen(false);
      playChime('send');
    } catch (error) {
      console.error("Failed to add room", error);
      showCustomAlert(t("Lỗi tạo phòng", "Create Room Error"), t("Không thể tạo phòng chat mới.", "Could not create new chat room."));
    }
  };

  // --- XỬ LÝ XÓA PHÒNG CHAT ---
  const handleDeleteRoom = (roomId: string, roomName: string) => {
    showCustomConfirm(
      t("Xóa phòng chat", "Delete Chat Room"),
      t(`Bạn có chắc chắn muốn xóa phòng chat "${roomName}"?`, `Are you sure you want to delete the chat room "${roomName}"?`),
      async () => {
        try {
          await axios.delete(`/admin/chat/rooms/${roomId}`);
          setRooms(prev => prev.filter(r => r.id !== roomId));
          setMessages(prev => {
            const copy = { ...prev };
            delete copy[roomId];
            return copy;
          });
          if (activeRoomId === roomId) {
            setActiveRoomId('staff');
          }
        } catch (error: any) {
          console.error("Failed to delete room", error);
          const errorMsg = error.response?.data?.error || t("Không thể xóa phòng chat này.", "Could not delete this chat room.");
          showCustomAlert(t("Lỗi xóa phòng", "Delete Room Error"), errorMsg);
        }
      }
    );
  };

  // --- XỬ LÝ THÊM THÀNH VIÊN VÀO PHÒNG CHAT ---
  const handleAddMemberToRoom = async (member: Member) => {
    try {
      const response = await axios.post(`/admin/chat/rooms/${activeRoomId}/members`, {
        user_id: member.id
      });

      const { member: newMember, system_message: sysMsg } = response.data;

      setRooms(prev => prev.map(r => {
        if (r.id === activeRoomId) {
          return {
            ...r,
            members: [...r.members, newMember]
          };
        }
        return r;
      }));

      if (sysMsg) {
        setMessages(prev => ({
          ...prev,
          [activeRoomId]: [...(prev[activeRoomId] || []), sysMsg]
        }));
      }

      playChime('receive');
    } catch (error: any) {
      console.error("Failed to add member", error);
      const errorMsg = error.response?.data?.error || t("Không thể thêm thành viên.", "Could not add member.");
      showCustomAlert(t("Lỗi thêm thành viên", "Add Member Error"), errorMsg);
    }
  };

  // --- XỬ LÝ CẬP NHẬT CHỨC VỤ THÀNH VIÊN TRONG PHÒNG CHAT ---
  const handleUpdateMemberRoomRole = async (memberId: number, newRole: 'leader' | 'co-leader' | 'member') => {
    try {
      const response = await axios.post(`/admin/chat/rooms/${activeRoomId}/role`, {
        user_id: memberId,
        role: newRole
      });

      const { system_message: sysMsg } = response.data;

      setRooms(prev => prev.map(r => {
        if (r.id === activeRoomId) {
          return {
            ...r,
            members: r.members.map(m => m.id === memberId ? { ...m, roomRole: newRole } : m)
          };
        }
        return r;
      }));

      if (sysMsg) {
        setMessages(prev => ({
          ...prev,
          [activeRoomId]: [...(prev[activeRoomId] || []), sysMsg]
        }));
      }

      setActiveMenuMemberId(null);
      playChime('receive');
    } catch (error: any) {
      console.error("Failed to update role", error);
      const errorMsg = error.response?.data?.error || t("Không thể cập nhật chức vụ.", "Could not update room role.");
      showCustomAlert(t("Lỗi cập nhật chức vụ", "Role Update Error"), errorMsg);
    }
  };

  // --- XỬ LÝ XÓA THÀNH VIÊN KHỎI PHÒNG CHAT ---
  const handleRemoveMemberFromRoom = (memberId: number, memberName: string) => {
    if (memberId === currentUserId) {
      showCustomAlert(
        t("Không thể thực hiện", "Action Denied"),
        t("Bạn không thể tự xóa bản thân khỏi phòng chat!", "You cannot remove yourself from the chat room!")
      );
      return;
    }

    let cleanMemberName = memberName.replace(' (Bạn)', '').replace(' (You)', '');

    showCustomConfirm(
      t("Xóa thành viên", "Remove Member"),
      t(`Bạn có chắc chắn muốn xóa ${cleanMemberName} khỏi phòng chat này?`, `Are you sure you want to remove ${cleanMemberName} from this chat room?`),
      async () => {
        try {
          const response = await axios.delete(`/admin/chat/rooms/${activeRoomId}/members/${memberId}`);
          const { system_message: sysMsg } = response.data;

          setRooms(prev => prev.map(r => {
            if (r.id === activeRoomId) {
              return {
                ...r,
                members: r.members.filter(m => m.id !== memberId)
              };
            }
            return r;
          }));

          if (sysMsg) {
            setMessages(prev => ({
              ...prev,
              [activeRoomId]: [...(prev[activeRoomId] || []), sysMsg]
            }));
          }

          setActiveMenuMemberId(null);
          playChime('send');
        } catch (error: any) {
          console.error("Failed to remove member", error);
          const errorMsg = error.response?.data?.error || t("Không thể xóa thành viên.", "Could not remove member.");
          showCustomAlert(t("Lỗi xóa thành viên", "Remove Member Error"), errorMsg);
        }
      }
    );
  };

  // --- XỬ LÝ THẢ CẢM XÚC (EMOJI REACTION) ---
  const handleAddReaction = async (msgId: string, emoji: string) => {
    try {
      const response = await axios.post(`/admin/chat/messages/${msgId}/react`, {
        emoji
      });

      const updatedReactions = response.data.reactions;

      setMessages(prev => {
        const roomMsgs = prev[activeRoomId] || [];
        const updated = roomMsgs.map(m => {
          if (m.id === msgId) {
            return {
              ...m,
              reactions: updatedReactions
            };
          }
          return m;
        });
        return { ...prev, [activeRoomId]: updated };
      });
    } catch (error) {
      console.error("Failed to toggle reaction", error);
    }
  };

  // --- XỬ LÝ CHỌN FILE ĐÍNH KÈM ---
  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setRawFile(file);

    const isImage = file.type.startsWith('image/');
    setSelectedFile({
      name: file.name,
      type: isImage ? 'image' : 'file',
      size: (file.size / 1024).toFixed(1) + ' KB',
      url: isImage ? URL.createObjectURL(file) : undefined
    });
  };

  // --- LỌC DANH SÁCH PHÒNG CHAT THEO TỪ KHÓA TÌM KIẾM ---
  const filteredRooms = rooms.filter(r => r.name.toLowerCase().includes(channelSearch.toLowerCase()));

  // Active room data
  const currentRoom = rooms.find(r => r.id === activeRoomId) || rooms[0];
  const roomMessages = messages[activeRoomId] || [];

  // Message filtering inside room
  const filteredMessages = messageSearch 
    ? roomMessages.filter(m => m.content.toLowerCase().includes(messageSearch.toLowerCase()))
    : roomMessages;

  return (
    <>
      {/* Lớp nền mờ phía sau khi mở khung chat (Overlay) */}
      {isOpen && (
        <div 
          onClick={onClose}
          className="fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-[9998] transition-all duration-300"
        />
      )}

      {/* Khung chứa Chat Room chính trượt ra từ bên phải */}
      <div className={`fixed inset-y-0 right-0 w-full md:w-[85vw] max-w-[1000px] bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl z-[9999] flex transition-all duration-300 ease-out font-sans ${isOpen ? 'translate-x-0' : 'translate-x-full'}`}>
        
        {/* CỘT 1: THANH BÊN (Danh sách các phòng chat và kênh liên lạc) */}
        {isDesktop || mobileView === 'rooms' ? (
          <div className="w-full md:w-[280px] md:min-w-[280px] md:max-w-[280px] border-r border-slate-100 dark:border-slate-800/80 flex flex-col shrink-0 bg-slate-50/50 dark:bg-slate-900/50">
          {/* Header */}
          <div className="p-5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                <MessageSquare size={18} />
              </div>
              <span className="font-black text-slate-800 dark:text-slate-100 uppercase tracking-tight text-xs">Comm Hub</span>
            </div>
            <div className="flex items-center gap-2">
              {/* Nút bật tắt âm thanh */}
              <button 
                onClick={() => setSoundEnabled(!soundEnabled)} 
                className="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-colors"
                title={soundEnabled ? 'Tắt âm thanh' : 'Bật âm thanh'}
              >
                {soundEnabled ? <Volume2 size={16} /> : <VolumeX size={16} />}
              </button>
              <button onClick={onClose} className="w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-colors">
                <X size={16} />
              </button>
            </div>
          </div>

          {/* Search Box */}
          <div className="p-4 border-b border-slate-100 dark:border-slate-800">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={14} />
              <input
                type="text"
                value={channelSearch}
                onChange={(e) => setChannelSearch(e.target.value)}
                placeholder="Tìm phòng chat..."
                className="w-full pl-9 pr-3 py-2 bg-slate-100/80 dark:bg-slate-800/80 border border-transparent focus:border-indigo-500 rounded-xl text-xs font-bold transition-all text-slate-800 dark:text-white outline-none"
              />
            </div>
          </div>

          {/* Danh sách các phòng chat */}
          <div className="flex-1 overflow-y-auto p-3 space-y-1">
            <div className="px-3 py-2 flex items-center justify-between">
              <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Danh sách kênh</span>
              <button 
                onClick={() => setIsAddRoomOpen(true)}
                className="w-5 h-5 rounded-md hover:bg-slate-200 dark:hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-colors"
                title={t("Thêm phòng mới", "Add new room")}
              >
                <Plus size={12} />
              </button>
            </div>
            {filteredRooms.map((room) => {
              const isActive = room.id === activeRoomId;
              const lastMsg = messages[room.id]?.[messages[room.id].length - 1];
              const isDeletable = room.id !== 'staff' && room.id !== 'announcement';

              return (
                <button
                  key={room.id}
                  onClick={() => {
                    setActiveRoomId(room.id);
                    setQuotedMessage(null);
                    setSelectedFile(null);
                    setIsMsgSearchOpen(false);
                    setMessageSearch('');
                    setIsAddingMember(false);
                    setMobileView('chat');
                  }}
                  className={`w-full text-left p-3 rounded-2xl flex items-center justify-between transition-all group ${isActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' : 'hover:bg-slate-100 dark:hover:bg-slate-800/50'}`}
                >
                  <div className="flex items-center gap-3 min-w-0 flex-1">
                    <div className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${isActive ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'}`}>
                      {room.type === 'private' ? <Lock size={16} className={isActive ? 'text-white' : 'text-purple-500'} /> : room.type === 'ai' ? <Sparkles size={16} className={isActive ? 'text-white' : 'text-blue-500'} /> : <MessageSquare size={16} />}
                    </div>
                    <div className="min-w-0 flex-1">
                      <div className={`text-xs font-black truncate ${isActive ? 'text-white' : 'text-slate-700 dark:text-slate-200'}`}>
                        {room.name}
                      </div>
                      <div className={`text-[10px] truncate mt-0.5 ${isActive ? 'text-indigo-200' : 'text-slate-400'}`}>
                        {lastMsg ? lastMsg.content : room.description}
                      </div>
                    </div>
                  </div>
                  
                  <div className="flex items-center gap-1.5 shrink-0 ml-2">
                    {/* Hành động xóa phòng - chỉ hiện với phòng có thể xóa */}
                    {isDeletable ? (
                      <span
                        onClick={(e) => {
                          e.stopPropagation();
                          handleDeleteRoom(room.id, room.name);
                        }}
                        className={`w-6 h-6 rounded-lg flex items-center justify-center transition-colors hover:bg-rose-500/20 text-rose-500 opacity-0 group-hover:opacity-100 ${isActive ? 'hover:bg-white/20 text-white' : ''}`}
                        title={t("Xóa phòng chat", "Delete chat room")}
                      >
                        <Trash2 size={12} />
                      </span>
                    ) : null}

                    {/* Hiển thị số lượng tin nhắn chưa đọc */}
                    {room.unreadCount > 0 && !isActive ? (
                      <span className="min-w-5 h-5 px-1.5 rounded-full bg-rose-600 text-white text-[9px] font-black flex items-center justify-center shadow-sm">
                        {room.unreadCount}
                      </span>
                    ) : null}
                  </div>
                </button>
              );
            })}
          </div>
        </div>
      ) : null}

        {/* CỘT 2: KHUNG TRÒ CHUYỆN ĐANG HOẠT ĐỘNG (Hiển thị nội dung chat của phòng hiện tại) */}
        {isDesktop || mobileView === 'chat' ? (
          <div className="w-full md:w-auto flex-grow md:flex-1 flex flex-col min-w-0 bg-white dark:bg-slate-950">
          
          {/* Phần đầu khung chat (Tiêu đề, nút quay lại trên mobile, search, thành viên) */}
          <div className="h-20 px-4 md:px-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-white/80 dark:bg-slate-950/80 backdrop-blur-md z-10">
            <div className="flex items-center gap-3 min-w-0 flex-1">
              <button 
                onClick={() => setMobileView('rooms')}
                className="md:hidden w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors shrink-0"
              >
                <ArrowLeft size={16} />
              </button>
              <div className="min-w-0 flex-1">
                <h3 className="text-xs md:text-sm font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
                  {currentRoom.name}
                  {currentRoom.type === 'private' && <Lock size={12} className="text-purple-500" />}
                  {currentRoom.type === 'ai' && <Sparkles size={12} className="text-blue-500" />}
                </h3>
                <p className="text-[9px] md:text-[10px] text-slate-400 truncate mt-0.5">{currentRoom.description}</p>
              </div>
            </div>
            
            <div className="flex items-center gap-2">
              {/* Nút tìm kiếm tin nhắn trong phòng chat hiện tại */}
              <button 
                onClick={() => setIsMsgSearchOpen(!isMsgSearchOpen)}
                className={`w-9 h-9 rounded-xl flex items-center justify-center transition-colors ${isMsgSearchOpen ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400'}`}
              >
                <Search size={16} />
              </button>
              
              {/* Nút bật/tắt danh sách thành viên của phòng chat */}
              <button 
                onClick={() => setShowMembers(!showMembers)}
                className={`w-9 h-9 rounded-xl flex items-center justify-center transition-colors ${showMembers ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400'}`}
              >
                <Users size={16} />
              </button>
            </div>
          </div>

          {/* Thanh tìm kiếm tin nhắn (hỗ trợ đóng mở co giãn) */}
          {isMsgSearchOpen ? (
            <div className="px-4 md:px-8 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-2 animate-in slide-in-from-top-2 duration-200">
              <Search size={14} className="text-slate-400" />
              <input
                type="text"
                value={messageSearch}
                onChange={(e) => setMessageSearch(e.target.value)}
                placeholder="Tìm nội dung tin nhắn..."
                className="flex-1 bg-transparent border-none outline-none text-xs font-bold text-slate-700 dark:text-white"
              />
              {messageSearch && (
                <button onClick={() => setMessageSearch('')} className="text-slate-400 hover:text-slate-600">
                  <X size={12} />
                </button>
              )}
            </div>
          ) : null}

          {/* Banner hiển thị tin nhắn được ghim lên đầu phòng chat */}
          {currentRoom.pinnedMessage ? (
            <div className="bg-amber-500/5 dark:bg-amber-500/10 border-b border-amber-500/10 px-4 md:px-8 py-2.5 flex items-start gap-2.5">
              <Pin size={14} className="text-amber-500 shrink-0 mt-0.5" />
              <div className="flex-1 min-w-0">
                <span className="text-[9px] font-black text-amber-500 uppercase tracking-wider block">Ghim bởi Quản trị</span>
                <span className="text-[10px] text-slate-600 dark:text-slate-300 truncate block mt-0.5">
                  {currentRoom.pinnedMessage}
                </span>
              </div>
            </div>
          ) : null}

          {/* Danh sách hiển thị các tin nhắn trong phòng chat */}
          <div className="flex-1 overflow-y-auto p-4 md:p-8 space-y-6">
            {filteredMessages.map((msg) => {
              if (msg.sender === 'Hệ thống') {
                return (
                  <div key={msg.id} className="flex justify-center my-2 animate-in fade-in duration-200">
                    <span className="bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 px-4 py-1.5 rounded-full text-[10px] font-bold shadow-sm border border-slate-200/10">
                      {msg.content}
                    </span>
                  </div>
                );
              }
              const isMe = isMessageFromMe(msg);
              return (
                <div key={msg.id} className={`flex items-start gap-3 group relative ${isMe ? 'flex-row-reverse' : ''}`}>
                  {/* Ảnh đại diện (Avatar) của người gửi */}
                  <div className={`w-9 h-9 rounded-xl flex items-center justify-center text-white shrink-0 text-xs font-black uppercase ${msg.avatarColor}`}>
                    {msg.sender.charAt(0)}
                  </div>

                  {/* Cấu trúc bong bóng tin nhắn */}
                  <div className="max-w-[85%] md:max-w-[70%] space-y-1">
                    {/* Tên người gửi và chức vụ/vai trò */}
                    <div className={`flex items-center gap-2 text-[10px] ${isMe ? 'justify-end' : ''}`}>
                      <span className="font-black text-slate-700 dark:text-slate-200">{msg.sender}</span>
                      <span className="text-slate-400">({msg.senderRole})</span>
                    </div>

                    {/* Thông tin tin nhắn gốc đang được trích dẫn (nếu có) */}
                    {msg.replyTo ? (
                      <div className="p-2 border-l-2 border-indigo-400 bg-slate-100 dark:bg-slate-800 rounded-lg text-[9px] text-slate-500 dark:text-slate-400 mb-1 max-w-full truncate">
                        <span className="font-bold text-slate-600 dark:text-slate-300 block">{msg.replyTo.sender}</span>
                        {msg.replyTo.content}
                      </div>
                    ) : null}

                    {/* Bong bóng nội dung tin nhắn */}
                    <div className={`p-3.5 rounded-2xl text-xs leading-relaxed break-words shadow-sm relative ${isMe ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-800 dark:text-slate-200 rounded-tl-none border border-slate-200/20'}`}>
                      {msg.content}

                      {/* Xem trước tệp đính kèm đi kèm tin nhắn (nếu có) */}
                      {msg.attachment ? (
                        <div className={`mt-2 p-2 border rounded-xl flex items-center gap-2.5 ${isMe ? 'border-white/20 bg-white/10' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'}`}>
                          {msg.attachment.type === 'image' ? (
                            <div className="w-10 h-10 rounded bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center">
                              <img src={msg.attachment.url} alt={msg.attachment.name} className="w-full h-full object-cover" />
                            </div>
                          ) : (
                            <div className="w-10 h-10 rounded bg-indigo-50 text-indigo-500 shrink-0 flex items-center justify-center">
                              <FileText size={18} />
                            </div>
                          )}
                          <div className="min-w-0">
                            <div className="text-[10px] font-bold truncate">{msg.attachment.name}</div>
                            <div className="text-[8px] opacity-60 mt-0.5">{msg.attachment.size}</div>
                          </div>
                        </div>
                      ) : null}
                    </div>

                    {/* Khung hiển thị các cảm xúc (Reactions) được thả dưới tin nhắn */}
                    {msg.reactions.length > 0 ? (
                      <div className={`flex flex-wrap gap-1.5 mt-1 ${isMe ? 'justify-end' : ''}`}>
                        {msg.reactions.map((react, i) => (
                          <button
                            key={i}
                            onClick={() => handleAddReaction(msg.id, react.emoji)}
                            className="inline-flex items-center gap-1.5 px-2 py-0.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-lg text-[9px] font-bold text-slate-500 hover:border-indigo-400 transition-colors"
                            title={react.users.join(', ')}
                          >
                            <span>{react.emoji}</span>
                            <span>{react.users.length}</span>
                          </button>
                        ))}
                      </div>
                    ) : null}

                    {/* Thời gian gửi tin nhắn và chỉ báo trạng thái đã đọc */}
                    <div className={`flex items-center gap-1 text-[8px] text-slate-400 mt-0.5 ${isMe ? 'justify-end' : ''}`}>
                      <span>{msg.time}</span>
                      {isMe ? (
                        <CheckCheck size={10} className={msg.isRead ? 'text-indigo-500' : 'text-slate-400'} />
                      ) : null}
                    </div>
                  </div>

                  {/* Thanh tác vụ nổi khi hover vào tin nhắn (thả biểu cảm, phản hồi) */}
                  <div className={`absolute top-1/2 -translate-y-1/2 hidden group-hover:flex items-center gap-1 p-1 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-md rounded-xl z-20 ${isMe ? 'left-4' : 'right-4'}`}>
                    <button 
                      onClick={() => handleAddReaction(msg.id, '👍')}
                      className="w-6 h-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-xs"
                      title="Like"
                    >
                      👍
                    </button>
                    <button 
                      onClick={() => handleAddReaction(msg.id, '❤️')}
                      className="w-6 h-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-xs"
                      title="Heart"
                    >
                      ❤️
                    </button>
                    <button 
                      onClick={() => setQuotedMessage(msg)}
                      className="w-6 h-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-colors"
                      title="Trả lời"
                    >
                      <Reply size={12} />
                    </button>
                  </div>
                </div>
              );
            })}

            {/* Hiệu ứng hiển thị dấu ba chấm động khi có thành viên đang gõ tin nhắn */}
            {typingUser ? (
              <div className="flex items-center gap-2.5 text-[10px] text-slate-400 italic pl-12 animate-pulse">
                <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce"></span>
                <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce delay-150"></span>
                <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce delay-300"></span>
                <span>{typingUser} {t('đang soạn tin...', 'is typing...')}</span>
              </div>
            ) : null}

            <div ref={messageEndRef} />
          </div>

          {/* Phần Chân Trang (Footer) - Nơi soạn thảo tin nhắn, đính kèm file */}
          <div className="p-4 md:p-6 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-950 shrink-0">
            {/* Thanh thông báo tin nhắn đang được trích dẫn để trả lời */}
            {quotedMessage ? (
              <div className="px-4 py-2 bg-indigo-50/50 dark:bg-indigo-950/20 border-l-2 border-indigo-500 rounded-xl flex items-center justify-between mb-3 animate-in slide-in-from-bottom-2 duration-150">
                <div className="min-w-0">
                  <span className="text-[9px] font-black text-indigo-600 dark:text-indigo-400 block uppercase tracking-wider">Đang trả lời {quotedMessage.sender}</span>
                  <span className="text-[10px] text-slate-600 dark:text-slate-300 truncate block mt-0.5">{quotedMessage.content}</span>
                </div>
                <button onClick={() => setQuotedMessage(null)} className="text-slate-400 hover:text-rose-600 transition-colors">
                  <X size={14} />
                </button>
              </div>
            ) : null}

            {/* Khung hiển thị và xem trước tệp đính kèm đã chọn */}
            {selectedFile ? (
              <div className="p-3 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-xl flex items-center justify-between mb-3 animate-in slide-in-from-bottom-2 duration-150">
                <div className="flex items-center gap-3 min-w-0">
                  {selectedFile.type === 'image' ? (
                    <div className="w-12 h-12 rounded bg-slate-100 overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700">
                      <img src={selectedFile.url} alt={selectedFile.name} className="w-full h-full object-cover" />
                    </div>
                  ) : (
                    <div className="w-12 h-12 rounded bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0">
                      <FileText size={20} />
                    </div>
                  )}
                  <div className="min-w-0">
                    <div className="text-xs font-bold truncate text-slate-700 dark:text-slate-300">{selectedFile.name}</div>
                    <div className="text-[10px] text-slate-400 mt-0.5">{selectedFile.size}</div>
                  </div>
                </div>
                <button onClick={() => setSelectedFile(null)} className="text-slate-400 hover:text-rose-600 transition-colors">
                  <X size={16} />
                </button>
              </div>
            ) : null}

            {/* Hàng chứa các công cụ nhập liệu chính */}
            <div className="flex items-center gap-3">
              {/* Thẻ chọn file ẩn phục vụ đính kèm tệp tin */}
              <input
                type="file"
                ref={fileInputRef}
                onChange={handleFileSelect}
                className="hidden"
              />
              <button 
                onClick={() => fileInputRef.current?.click()}
                className="w-11 h-11 border-2 border-slate-100 dark:border-slate-800 hover:border-indigo-500 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-all shrink-0 active:scale-95"
                title="Đính kèm file/hình ảnh"
              >
                <Paperclip size={18} />
              </button>

              {/* Ô nhập tin nhắn văn bản */}
              <div className="flex-1 relative">
                <input
                  type="text"
                  value={inputText}
                  onChange={(e) => setInputText(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') handleSendMessage();
                  }}
                  disabled={currentRoom.type === 'announcement' && activeRoomId !== 'announcement'}
                  placeholder={
                    currentRoom.type === 'announcement' && activeRoomId !== 'announcement'
                      ? 'Chỉ có Quản trị viên mới được gửi tin vào kênh này.'
                      : 'Nhập nội dung tin nhắn... (Nhấn Enter để gửi)'
                  }
                  className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-900 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-950 rounded-xl text-xs font-bold transition-all text-slate-800 dark:text-white outline-none"
                />
              </div>

              {/* Nút gửi tin nhắn */}
              <button 
                onClick={handleSendMessage}
                disabled={currentRoom.type === 'announcement' && activeRoomId !== 'announcement'}
                className="w-11 h-11 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:text-slate-400 text-white rounded-xl flex items-center justify-center transition-all shrink-0 active:scale-95 shadow-md shadow-indigo-600/10"
              >
                <Send size={16} />
              </button>
            </div>
          </div>
        </div>
      ) : null}

        {/* CỘT 3: DANH SÁCH THÀNH VIÊN TRONG PHÒNG (Co giãn được) */}
        {showMembers ? (
          <div className="absolute right-0 top-0 bottom-0 w-[240px] z-30 border-l border-slate-100 dark:border-slate-800/80 shrink-0 flex flex-col bg-white dark:bg-slate-900 md:bg-slate-50/50 md:dark:bg-slate-900/50 shadow-2xl md:shadow-none md:relative md:flex animate-in slide-in-from-right duration-300">
            <div className="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <span className="font-black text-slate-800 dark:text-slate-100 uppercase tracking-tight text-xs">Thành viên ({currentRoom.members.length})</span>
              <button onClick={() => setShowMembers(false)} className="text-slate-400 hover:text-slate-600">
                <X size={14} />
              </button>
            </div>
            
            {/* Nút thêm thành viên mới */}
            <div className="p-3 border-b border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50">
              <button
                onClick={() => setIsAddingMember(!isAddingMember)}
                className="w-full py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 transition-colors"
              >
                <UserCheck size={14} />
                {t("Thêm thành viên", "Add member")}
              </button>
            </div>

            {/* Khu vực lựa chọn những thành viên chưa tham gia phòng */}
            {isAddingMember ? (
              <div className="p-3 bg-slate-100/50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 space-y-2 animate-in slide-in-from-top-2 duration-200 shrink-0">
                <span className="text-[9px] font-black text-slate-400 uppercase tracking-wider block mb-1">
                  {t("Chọn thành viên để thêm:", "Select member to add:")}
                </span>
                {(() => {
                  const membersNotInRoom = allEmployees.map(m => {
                    let cleanName = m.name.replace(' (Bạn)', '').replace(' (You)', '');
                    if (m.id === currentUserId) {
                      return { ...m, name: `${cleanName} (${t('Bạn', 'You')})` };
                    }
                    return { ...m, name: cleanName };
                  }).filter(m => !currentRoom.members.some(existing => existing.id === m.id));

                  if (membersNotInRoom.length === 0) {
                    return (
                      <span className="text-[10px] text-slate-400 italic block text-center py-1">
                        {t("Mọi người đều đã tham gia", "Everyone has joined")}
                      </span>
                    );
                  }
                  return (
                    <div className="max-h-[150px] overflow-y-auto space-y-1 pr-1">
                      {membersNotInRoom.map(m => (
                        <button
                          key={m.id}
                          onClick={() => handleAddMemberToRoom(m)}
                          className="w-full text-left p-2 rounded-xl hover:bg-white dark:hover:bg-slate-800 flex items-center gap-2 transition-all border border-transparent hover:border-slate-200/50"
                        >
                          <div className={`w-6 h-6 rounded-lg flex items-center justify-center text-white text-[10px] font-black uppercase shrink-0 ${m.avatarColor}`}>
                            {m.name.charAt(0)}
                          </div>
                          <div className="min-w-0 flex-1">
                            <div className="text-[10px] font-bold text-slate-700 dark:text-slate-200 truncate">{m.name}</div>
                            <div className="text-[8px] text-slate-400 mt-0.5">{m.role}</div>
                          </div>
                        </button>
                      ))}
                    </div>
                  );
                })()}
              </div>
            ) : null}

            <div className="flex-1 overflow-y-auto p-4 space-y-4">
              {currentRoom.members.map((member) => {
                const isMe = member.id === currentUserId;
                const isMenuOpen = activeMenuMemberId === member.id;
                
                const roomRoleLabel = member.roomRole === 'leader' 
                  ? t("Trưởng nhóm", "Leader") 
                  : member.roomRole === 'co-leader' 
                    ? t("Phó nhóm", "Co-leader") 
                    : null;
                
                const showActions = canManageMember(member, currentRoom.members);
                
                return (
                  <div key={member.id} className="flex items-center justify-between group/member relative">
                    <div className="flex items-center gap-3 min-w-0">
                      {/* Phần bao quanh ảnh đại diện (Avatar) */}
                      <div className="relative shrink-0">
                        <div className={`w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-black uppercase ${member.avatarColor}`}>
                          {member.name.charAt(0)}
                        </div>
                        {/* Dấu chấm biểu thị trạng thái online/offline */}
                        <span className={`absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-900 ${member.status === 'online' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'}`}></span>
                      </div>
                      
                      <div className="min-w-0">
                        <div className="flex items-center gap-1.5">
                          <span className="text-[11px] font-bold text-slate-700 dark:text-slate-200 truncate block max-w-[110px]">{member.name}</span>
                          {roomRoleLabel && (
                            <span className={`px-1 py-0.5 rounded text-[7px] font-black uppercase tracking-wider shrink-0 ${
                              member.roomRole === 'leader' 
                                ? 'bg-indigo-500 text-white' 
                                : 'bg-amber-500 text-white'
                            }`}>
                              {roomRoleLabel}
                            </span>
                          )}
                        </div>
                        <div className="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{member.role}</div>
                      </div>
                    </div>
 
                    {/* Nút hành động trên thành viên */}
                    {showActions ? (
                      <div className="relative shrink-0">
                        <button
                          onClick={() => setActiveMenuMemberId(isMenuOpen ? null : member.id)}
                          className="w-6 h-6 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                          title={t("Quản lý thành viên", "Manage member")}
                        >
                          <i className="fa-solid fa-ellipsis-vertical text-[10px]"></i>
                        </button>
 
                        {/* Thực đơn thả xuống (Dropdown) để phân vai trò hoặc xóa thành viên */}
                        {isMenuOpen ? (
                          <div className="absolute right-0 mt-1 w-40 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-1 z-[9999] animate-in fade-in slide-in-from-top-1 duration-150">
                            <div className="px-2.5 py-1.5 border-b border-slate-100 dark:border-slate-700">
                              <span className="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest block">Vai trò trong room</span>
                            </div>
                            {(userRoleId === 1 || (currentRoom.members.find(m => m.id === currentUserId)?.roomRole === 'leader')) ? (
                              <button
                                onClick={() => handleUpdateMemberRoomRole(member.id, 'leader')}
                                className={`w-full text-left px-2.5 py-1.5 text-[10px] font-bold flex items-center gap-1.5 hover:bg-slate-100 dark:hover:bg-slate-700/50 ${member.roomRole === 'leader' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-300'}`}
                              >
                                <span>👑</span>
                                <span>{t("Trưởng nhóm", "Leader")}</span>
                              </button>
                            ) : null}
                            <button
                              onClick={() => handleUpdateMemberRoomRole(member.id, 'co-leader')}
                              className={`w-full text-left px-2.5 py-1.5 text-[10px] font-bold flex items-center gap-1.5 hover:bg-slate-100 dark:hover:bg-slate-700/50 ${member.roomRole === 'co-leader' ? 'text-amber-500' : 'text-slate-600 dark:text-slate-300'}`}
                            >
                              <span>🛡️</span>
                              <span>{t("Phó nhóm", "Co-leader")}</span>
                            </button>
                            <button
                              onClick={() => handleUpdateMemberRoomRole(member.id, 'member')}
                              className={`w-full text-left px-2.5 py-1.5 text-[10px] font-bold flex items-center gap-1.5 hover:bg-slate-100 dark:hover:bg-slate-700/50 ${(!member.roomRole || member.roomRole === 'member') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-300'}`}
                            >
                              <span>👤</span>
                              <span>{t("Thành viên", "Member")}</span>
                            </button>
                            
                            <div className="h-px bg-slate-100 dark:bg-slate-700 my-1" />
                            <button
                              onClick={() => handleRemoveMemberFromRoom(member.id, member.name)}
                              className="w-full text-left px-2.5 py-1.5 text-[10px] font-bold flex items-center gap-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20"
                            >
                              <span>❌</span>
                              <span>{t("Xóa khỏi phòng", "Remove from room")}</span>
                            </button>
                          </div>
                        ) : null}
                      </div>
                    ) : null}
                  </div>
                );
              })}
            </div>
          </div>
        ) : null}

        {/* Modal Thêm Phòng Chat */}
        {isAddRoomOpen ? (
          <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center z-[99999] p-6 animate-in fade-in duration-200">
            <div className="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-2xl space-y-4 animate-in zoom-in-95 duration-200">
              <div className="flex items-center justify-between">
                <h3 className="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-tight">
                  {t("Thêm phòng chat mới", "Create New Chat Room")}
                </h3>
                <button 
                  onClick={() => {
                    setIsAddRoomOpen(false);
                    setNewRoomName('');
                    setNewRoomDesc('');
                    setNewRoomType('group');
                  }}
                  className="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-colors"
                >
                  <X size={16} />
                </button>
              </div>

              <div className="space-y-3">
                <div>
                  <label className="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-1">
                    {t("Tên phòng", "Room Name")}
                  </label>
                  <input
                    type="text"
                    value={newRoomName}
                    onChange={(e) => setNewRoomName(e.target.value)}
                    placeholder={t("Ví dụ: Kênh Marketing", "e.g., Marketing Channel")}
                    className="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 focus:border-indigo-500 rounded-xl text-xs font-bold transition-all text-slate-800 dark:text-white outline-none"
                  />
                </div>

                <div>
                  <label className="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-1">
                    {t("Mô tả", "Description")}
                  </label>
                  <textarea
                    value={newRoomDesc}
                    onChange={(e) => setNewRoomDesc(e.target.value)}
                    placeholder={t("Mô tả ngắn về phòng chat...", "Short description of the room...")}
                    rows={2}
                    className="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 focus:border-indigo-500 rounded-xl text-xs font-bold transition-all text-slate-800 dark:text-white outline-none resize-none"
                  />
                </div>

                <div>
                  <label className="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-1">
                    {t("Loại phòng", "Room Type")}
                  </label>
                  <div className="grid grid-cols-2 gap-2">
                    <button
                      type="button"
                      onClick={() => setNewRoomType('group')}
                      className={`py-2 px-3 rounded-xl border text-xs font-bold transition-all ${newRoomType === 'group' ? 'border-indigo-600 bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800'}`}
                    >
                      {t("Công khai / Nhóm", "Group / Public")}
                    </button>
                    <button
                      type="button"
                      onClick={() => setNewRoomType('private')}
                      className={`py-2 px-3 rounded-xl border text-xs font-bold transition-all ${newRoomType === 'private' ? 'border-indigo-600 bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800'}`}
                    >
                      {t("Riêng tư", "Private")}
                    </button>
                  </div>
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => {
                    setIsAddRoomOpen(false);
                    setNewRoomName('');
                    setNewRoomDesc('');
                    setNewRoomType('group');
                  }}
                  className="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
                >
                  {t("Hủy bỏ", "Cancel")}
                </button>
                <button
                  type="button"
                  onClick={handleAddRoom}
                  className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors shadow-md shadow-indigo-600/10"
                >
                  {t("Tạo phòng", "Create Room")}
                </button>
              </div>
            </div>
          </div>
        ) : null}
        {/* Hộp thoại thông báo và xác nhận tùy chỉnh (Custom Dialog/Alert/Confirm Modal) */}
        {dialog && dialog.isOpen ? (
          <div className="fixed inset-0 bg-slate-950/40 backdrop-blur-md flex items-center justify-center z-[999999] p-6 animate-in fade-in duration-300">
            <div className="w-full max-w-sm bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-[2.5rem] border border-white/20 dark:border-slate-800/80 p-8 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] space-y-6 animate-in zoom-in-95 duration-300">
              <div className="flex flex-col items-center text-center space-y-4">
                <div className={`w-16 h-16 rounded-[1.75rem] flex items-center justify-center shrink-0 shadow-inner ${
                  dialog.title.toLowerCase().includes('xóa') || dialog.title.toLowerCase().includes('delete') || dialog.title.toLowerCase().includes('remove') || dialog.title.toLowerCase().includes('không')
                    ? 'bg-rose-500/10 text-rose-500 border border-rose-500/20' 
                    : 'bg-indigo-500/10 text-indigo-500 border border-indigo-500/20'
                }`}>
                  {dialog.title.toLowerCase().includes('xóa') || dialog.title.toLowerCase().includes('delete') || dialog.title.toLowerCase().includes('remove') || dialog.title.toLowerCase().includes('không') ? (
                    <Trash2 size={26} className="animate-pulse" />
                  ) : (
                    <Users size={26} className="animate-pulse" />
                  )}
                </div>
                <div className="space-y-2">
                  <h3 className="text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest leading-none">
                    {dialog.title}
                  </h3>
                  <p className="text-xs text-slate-500 dark:text-slate-400 font-bold leading-relaxed pt-1">
                    {dialog.message}
                  </p>
                </div>
              </div>

              <div className="flex flex-col gap-2 pt-2">
                <button
                  type="button"
                  onClick={dialog.onConfirm}
                  className={`w-full py-3.5 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95 shadow-lg ${
                    dialog.title.toLowerCase().includes('xóa') || dialog.title.toLowerCase().includes('delete') || dialog.title.toLowerCase().includes('remove')
                      ? 'bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 shadow-rose-600/20 hover:shadow-rose-600/30'
                      : 'bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 shadow-indigo-600/20 hover:shadow-indigo-600/30'
                  }`}
                >
                  {dialog.type === 'confirm' ? t("Xác nhận hành động", "Confirm Action") : t("Đồng ý", "OK")}
                </button>
                {dialog.type === 'confirm' ? (
                  <button
                    type="button"
                    onClick={dialog.onCancel}
                    className="w-full py-3.5 text-[10px] font-black text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 transition-all duration-300 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/40 dark:hover:bg-slate-800/80 rounded-2xl active:scale-95 uppercase tracking-widest"
                  >
                    {t("Hủy bỏ", "Cancel")}
                  </button>
                ) : null}
              </div>
            </div>
          </div>
        ) : null}
      </div>
    </>
  );
}
