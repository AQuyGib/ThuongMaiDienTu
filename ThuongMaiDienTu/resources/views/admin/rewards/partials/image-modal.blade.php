<div id="reward-image-modal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center z-[9999] p-4 transition-all">
    <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl animate-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
            <h3 class="text-xl font-extrabold text-slate-800">{{ app()->getLocale() === 'en' ? 'Update Reward Image' : 'Cập nhật ảnh reward' }}</h3>
            <button type="button" onclick="closeImageModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="reward-image-form" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ app()->getLocale() === 'en' ? 'Select new image from device' : 'Chọn ảnh mới từ thiết bị' }}</label>
                    <input name="image" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm outline-none transition bg-slate-50/50 text-slate-600 font-semibold file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer">
                </div>
                <div class="rounded-2xl overflow-hidden bg-slate-50 border border-dashed border-slate-200 h-56 flex items-center justify-center">
                    <img id="reward-image-preview" src="" alt="preview" class="w-full h-full object-cover hidden">
                    <span id="reward-image-placeholder" class="text-sm text-slate-400 italic">{{ app()->getLocale() === 'en' ? 'No image selected' : 'Chưa chọn ảnh' }}</span>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 mt-4">
                <button type="button" onclick="closeImageModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition">{{ app()->getLocale() === 'en' ? 'Close' : 'Đóng' }}</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition shadow-lg shadow-indigo-100">{{ app()->getLocale() === 'en' ? 'Update Image' : 'Cập nhật ảnh' }}</button>
            </div>
        </form>
    </div>
</div>
