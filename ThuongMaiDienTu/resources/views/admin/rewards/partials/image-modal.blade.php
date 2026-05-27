<div id="reward-image-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl p-6 max-w-lg w-full">
        <h3 class="text-xl font-bold mb-4">Cập nhật ảnh reward</h3>
        <form id="reward-image-form" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Chọn ảnh mới</label>
                    <input name="image" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="w-full border rounded-xl p-3">
                </div>
                <div class="rounded-2xl overflow-hidden bg-slate-100 h-56 flex items-center justify-center">
                    <img id="reward-image-preview" src="" alt="preview" class="w-full h-full object-cover hidden">
                    <span id="reward-image-placeholder" class="text-sm text-slate-400">Chưa chọn ảnh</span>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="closeImageModal()" class="px-4 py-2 rounded-xl bg-slate-100">Đóng</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Cập nhật ảnh</button>
            </div>
        </form>
    </div>
</div>
