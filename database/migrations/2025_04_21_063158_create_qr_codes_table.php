<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('content'); // Trường Data (đổi tên thành content cho rõ nghĩa nội dung mã QR)
            $table->string('local_link')->nullable(); // img hoặc local_link (lưu đường dẫn file ảnh local)
            $table->foreignId('created_by')->nullable()->constrained('users'); // create_by (khóa ngoại đến bảng users)
            $table->string('redirect_to')->nullable(); // redirect_to (liên kết chuyển hướng lưu kèm)
            $table->timestamps();
            $table->softDeletes(); // delete_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
