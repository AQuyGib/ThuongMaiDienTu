                                    <a href="{{ route('admin.categories.translation.edit', $category->category_id) }}"
                                       class="icon-btn"
                                       title="Edit EN Translation">
                                        <i class="fa-solid fa-language"></i>
                                    </a>
                                    <button type="button" class="icon-btn js-edit-category"
                                            data-id="{{ $category->category_id }}"
                                            data-name="{{ e($category->name) }}"
                                            data-parent-id="{{ $category->parent_id ?? '' }}"
                                            title="Sửa">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="icon-btn danger js-delete-category"
                                            data-id="{{ $category->category_id }}"
                                            data-name="{{ e($category->name) }}"
                                            title="Xóa">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
