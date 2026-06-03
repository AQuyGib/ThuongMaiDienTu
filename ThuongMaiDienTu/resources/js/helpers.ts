/**
 * Kiểm tra xem giao diện hiện tại của ứng dụng có đang hiển thị bằng Tiếng Anh (en) hay không.
 * Phương thức này đọc trực tiếp thuộc tính "lang" của thẻ <html> (Ví dụ: <html lang="en">).
 * 
 * @returns boolean true nếu đang ở giao diện Tiếng Anh, ngược lại là false.
 */
export const isEn = (): boolean => {
    return document.documentElement.lang === 'en';
};

/**
 * Hàm dịch thuật nhanh (Inline translation helper) dành cho giao diện React/TSX.
 * 
 * @param vi Nội dung hiển thị Tiếng Việt
 * @param en Nội dung hiển thị Tiếng Anh
 * @returns string Chuỗi văn bản tương ứng với ngôn ngữ giao diện đang hoạt động.
 */
export const t = (vi: string, en: string): string => {
    return isEn() ? en : vi;
};
