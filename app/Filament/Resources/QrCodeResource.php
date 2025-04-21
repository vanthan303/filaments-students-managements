<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QrCodeResource\Pages;
use App\Filament\Resources\QrCodeResource\RelationManagers;
use App\Models\QrCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http; // Thêm dòng này
use Illuminate\Support\Facades\Storage; // Thêm dòng này
use Illuminate\Support\Str; // Thêm dòng này
use Filament\Tables\Columns\ImageColumn; // Thêm dòng này
use Filament\Tables\Columns\TextColumn; // Thêm dòng này
use Filament\Forms\Components\TextInput; // Thêm dòng này
use Filament\Forms\Components\Section; // Thêm dòng này
use Filament\Notifications\Notification; // Thêm dòng này cho thông báo lỗi


class QrCodeResource extends Resource
{
    protected static ?string $model = QrCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    /* Tạo ra 1 Khối block mới với tên 'QRCode' chứa Module Student */
    protected static ?string $navigationGroup = 'QRCode';

    /* Thay đổi tên cho phần breadcrumb & button New, câu thông báo khi list rỗng */
    protected static ?string $modelLabel = 'Mã QR';
    protected static ?string $pluralModelLabel = 'Danh Sách Các Mã QR';

    /**
     * Hàm tạo ra form nhập liệu
     * Áp dụng cho các trang create, edit
     *
    */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin tạo Mã QR')
                    ->schema([
                        TextInput::make('content')
                            ->required()
                            ->label('Nội dung QR Code')
                            ->placeholder('Ví dụ: https://your-website.com hoặc Đoạn văn bản')
                            ->helperText('Nhập nội dung (URL, văn bản,...) mà bạn muốn mã QR mã hóa.'),

                        TextInput::make('redirect_to')
                            ->label('Liên kết chuyển hướng (tùy chọn)')
                            ->placeholder('Ví dụ: https://another-page.com')
                            ->helperText('Lưu lại một liên kết liên quan, không nhất thiết phải là nội dung của QR Code.'),

                        // Các trường 'local_link' và 'created_by' sẽ được điền tự động trong quá trình lưu
                    ])
                    ->columns(1), // Hiển thị các trường theo 1 cột
            ]);
    }



    /**
     * Hàm hiển thị danh sách các bản ghi trong bảng
     * Tạo ra các cột, bộ lọc, các button Edit, Delete
     *
    */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content')
                    ->label('Nội dung')
                    ->searchable()
                    ->sortable(),

                // Hiển thị ảnh QR code từ đường dẫn local
                ImageColumn::make('local_link')
                    ->label('Hình ảnh QR Code')
                    ->disk('public') // Cấu hình disk storage, đảm bảo 'public' disk trỏ đúng đến thư mục public/storage
                    ->square() // Hiển thị ảnh vuông
                    ->size(80), // Kích thước hiển thị

                // Hiển thị tên người tạo (nếu có quan hệ user)
                TextColumn::make('user.name')
                    ->label('Người tạo')
                    ->sortable(),
                    //->toggleable(isToggledHiddenByDefault: true), // Ẩn mặc định

                TextColumn::make('redirect_to')
                    ->label('Liên kết chuyển hướng')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false), // True : Ẩn (mặc định) | False : Không ẩn

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ẩn mặc định

                TextColumn::make('updated_at')
                    ->label('Ngày cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ẩn mặc định
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(), // Cho phép lọc các bản ghi đã xóa mềm
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Nút xem chi tiết
                Tables\Actions\EditAction::make(), // Nút chỉnh sửa
                Tables\Actions\DeleteAction::make(), // Nút "Delete" xóa mềm
                Tables\Actions\ForceDeleteAction::make(), // Nút "Force delete" xóa vĩnh viễn
                Tables\Actions\RestoreAction::make(), // Nút "Restore" khôi phục (cho bản ghi đã xóa mềm)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Xóa mềm nhiều bản ghi
                    Tables\Actions\ForceDeleteBulkAction::make(), // Xóa vĩnh viễn nhiều bản ghi
                    Tables\Actions\RestoreBulkAction::make(), // Khôi phục nhiều bản ghi
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //s
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQrCodes::route('/'),
            'create' => Pages\CreateQrCode::route('/create'),
            'edit' => Pages\EditQrCode::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Bao gồm cả các bản ghi đã xóa mềm khi truy vấn
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
