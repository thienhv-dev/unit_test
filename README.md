# Unit Test Project

## Giới thiệu
Đây là source code mẫu cho hệ thống xử lý đơn hàng với các lớp dịch vụ, enum, và unit test sử dụng PHPUnit.

## Yêu cầu hệ thống
- PHP >= 7.4
- Composer
- PHPUnit >= 9.x

## Cài đặt
1. **Clone repository:**
   ```bash
   git clone <repository-url>
   cd unit_test
   ```
2. **Cài đặt Composer:**
   Nếu chưa có Composer, cài đặt tại https://getcomposer.org/

3. **Cài đặt dependencies:**
   ```bash
   composer install
   ```

## Cấu trúc thư mục
- `app/` - Chứa mã nguồn chính (các class, enum, service)
- `tests/` - Chứa các file unit test
- `coverage/` - Báo cáo coverage sau khi chạy test
- `phpunit.xml` - File cấu hình PHPUnit

## Chạy Unit Test
```bash
./vendor/bin/phpunit --configuration phpunit.xml
```

## Sinh báo cáo Coverage (HTML)
```bash
./vendor/bin/phpunit --coverage-html coverage
```
Sau khi chạy, mở file `coverage/index.html` để xem báo cáo coverage.

## Ghi chú
- Đảm bảo PHP và Composer đã được cài đặt đúng phiên bản.
- Có thể cần cấp quyền thực thi cho file PHPUnit: `chmod +x ./vendor/bin/phpunit`

## Liên hệ
Mọi thắc mắc vui lòng liên hệ chủ repository.
