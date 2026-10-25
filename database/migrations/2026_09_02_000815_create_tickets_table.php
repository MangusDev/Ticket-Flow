<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('area_id')->constrained('areas')->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('status', TicketStatus::values())->default(TicketStatus::OPEN->value);
            $table->enum('priority', TicketPriority::values())->default(TicketPriority::LOW->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
