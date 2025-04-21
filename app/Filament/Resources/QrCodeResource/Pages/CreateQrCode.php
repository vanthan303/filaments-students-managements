<?php

namespace App\Filament\Resources\QrCodeResource\Pages;

use App\Filament\Resources\QrCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Filament\Notifications\Notification; // Thêm dòng này
use App\Models\QrCode; // Thêm dòng này

class CreateQrCode extends CreateRecord
{
    protected static string $resource = QrCodeResource::class;

    // Phương thức này được gọi trước khi bản ghi được lưu vào database

    /**
     * Hàm Tạo mã QRCode thành công
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $content = $data['content']; // Lấy nội dung từ form

        // --- Gọi API QR Code Monkey ---
        $apiUrl = 'https://api.qrcode-monkey.com/qr/custom';

        // Các tham số cần gửi đến API.
        // Tham khảo thêm tài liệu QR Code Monkey API để biết các tùy chọn khác (màu sắc, logo,...)
        $apiParams = [
            'data' => $content, // Nội dung mã hóa
            'config' => [
                'body' => 'circle',
                'logo' => '#facebook'
            ],
            'size' => 300, // Kích thước ảnh (pixel)
            'download' => false, // Yêu cầu API trả về dữ liệu ảnh trực tiếp thay vì link download
            'file' => 'png', // Định dạng ảnh (png hoặc svg)

        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'image/png', // Chấp nhận phản hồi là ảnh PNG
            ])->post($apiUrl, $apiParams);

            // Kiểm tra xem API call có thành công không và có dữ liệu ảnh trả về không
            if ($response->successful() && $response->body()) {
                // --- Lưu ảnh QR Code vào Storage cục bộ ---
                // Tạo tên file duy nhất
                $filename = 'qrcodes/' . Str::uuid() . '.png';

                // Lưu nội dung ảnh nhận được từ API vào public disk
                Storage::disk('public')->put($filename, $response->body());

                // Lấy đường dẫn công khai (URL) của file ảnh đã lưu
                $data['local_link'] = Storage::disk('public')->url($filename);

            } else {
                // Xử lý trường hợp API trả về lỗi hoặc không có dữ liệu ảnh
                $errorMessage = $response->body() ?: 'Lỗi không xác định từ API QR Code Monkey.';

                // Gửi thông báo lỗi Filament cho người dùng
                Notification::make()
                    ->title('Lỗi tạo QR Code')
                    ->body('Không thể tạo hình ảnh QR Code từ API: ' . $errorMessage)
                    ->danger()
                    ->send();

                // Ngừng quá trình lưu bản ghi nếu không tạo được ảnh
                $this->halt(); // Filament helper to stop the save process
                return $data; // Trả về data nhưng quá trình lưu đã bị dừng
            }

        } catch (\Exception $e) {
            // Xử lý các lỗi ngoại lệ (ví dụ: mất mạng, lỗi server API,...)
             Notification::make()
                    ->title('Lỗi hệ thống')
                    ->body('Đã xảy ra lỗi trong quá trình gọi API hoặc lưu file: ' . $e->getMessage())
                    ->danger()
                    ->send();

             $this->halt();
             return $data;
        }


        // --- Thêm các trường dữ liệu khác trước khi lưu ---
        // Thêm ID của người dùng hiện tại (nếu user đã đăng nhập)
        $data['created_by'] = auth()->id(); // auth()->id() trả về null nếu không có user đăng nhập

        return $data;
    }


    /**
     * Chuyển hướng sau khi tạo mã QRCode thành công
     */
    protected function getRedirectUrl(): string
    {
        // Chuyển hướng về trang danh sách sau khi tạo thành công
        return $this->getResource()::getUrl('index');
    }
}
