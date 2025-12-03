<?php

namespace Tests\Feature;

use App\Models\Booking\Booking;
use App\Models\Finance\Invoice;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyCastTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_set_money_attribute_using_integer_cents()
    {
        $booking = Booking::factory()->create([
            'total_price_amount' => 1000, // 10.00 CZK
            'currency' => 'CZK',
        ]);

        $this->assertInstanceOf(Money::class, $booking->total_price_amount);
        $this->assertEquals(1000, $booking->total_price_amount->getAmount());
        $this->assertEquals('10,00 Kč', $booking->total_price_amount->format());
    }

    /** @test */
    public function it_can_set_money_attribute_using_money_object()
    {
        $money = new Money(2000, new \Akaunting\Money\Currency('CZK'));

        $booking = Booking::factory()->create([
            'total_price_amount' => $money,
        ]);

        $this->assertEquals(2000, $booking->total_price_amount->getAmount());
    }

    /** @test */
    public function it_throws_exception_when_setting_float_value()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MoneyCast: Float values are not allowed');

        $booking = new Booking();
        $booking->total_price_amount = 10.50;
    }

    /** @test */
    public function it_throws_exception_when_setting_invalid_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MoneyCast: Invalid type');

        $booking = new Booking();
        $booking->total_price_amount = 'invalid';
    }

    /** @test */
    public function it_accepts_numeric_string_as_integer()
    {
        $booking = Booking::factory()->create([
            'total_price_amount' => '3000',
        ]);

        $this->assertEquals(3000, $booking->total_price_amount->getAmount());
    }


}
