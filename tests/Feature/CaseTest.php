<?php

namespace EvangGeo\CaseStatement\Tests\Feature;

use EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder\LogicalBuilder;
use EvangGeo\CaseStatement\Tests\Models\Order;
use EvangGeo\CaseStatement\Tests\TestCase;
use Throwable;

class CaseTest extends TestCase
{
    public function validateBindings(array $bindings, int $count, array $bindingValues)
    {
        $this->assertCount($count, $bindings, 'the count of binding is not correct');

        collect($bindingValues)->each(function ($v, $index) use ($bindings) {
            $this->assertSame($v, $bindings[$index]);
        });
    }

    /**
     * @throws Throwable
     */
    public function test_simple_case_method()
    {
        $query = Order::query()->case([
            when('type_id', 1)->then('Deposit'),
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? then ? else ? end) as `type` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 3, [1, 'Deposit', 'N/A']);
    }

    public function test_case_with_and_condition()
    {
        $query = Order::query()->case([
            when('type_id', 1)->and('status_id', 1)->then('Deposit'),
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? and ( `status_id` = ? ) then ? else ? end) as `type` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 4, [1, 1, 'Deposit', 'N/A']);

        $query = Order::query()->case([
            when('type_id', 1)->and(['status_id' => 1, 'amount' => 2])->then('Deposit'),
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? and ( `status_id` = ? and `amount` = ? ) then ? else ? end) as `type` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 5, [1, 1, 2, 'Deposit', 'N/A']);
    }

    public function test_case_with_or_condition()
    {
        $query = Order::query()->case([
            when('type_id', 1)->or('status_id', 1)->then('Deposit'),
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? or ( `status_id` = ? ) then ? else ? end) as `type` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 4, [1, 1, 'Deposit', 'N/A']);

        $query = Order::query()->case([
            when('type_id', 1)->or(['status_id' => 1, 'amount' => 2])->then('Deposit'),
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? or ( `status_id` = ? or `amount` = ? ) then ? else ? end) as `type` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 5, [1, 1, 2, 'Deposit', 'N/A']);
    }

    public function test_case_with_and_and_or_conditions()
    {
        $query = Order::query()->case([
            when('type_id', 1)->and('amount', 2)->or('status_id', 1)->then('Deposit'),
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? and ( `amount` = ? ) or ( `status_id` = ? ) then ? else ? end) as `type` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 5, [1, 2, 1, 'Deposit', 'N/A']);
    }

    public function test_case_with_multiple_conditions()
    {
        $query = Order::query()->case([
            when('type_id', 1)->and(['amount' => 1, 'status' => 'complete'])->or(['amount' => 2, 'status' => 'initiated'])->then('success')
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? and ( `amount` = ? and `status` = ? ) or ( `amount` = ? or `status` = ? ) then ? else ? end) as `type` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 7, [1, 1, 'complete', 2, 'initiated', 'success', 'N/A']);
    }

    public function test_case_with_multiple_when_conditions()
    {
        $query = Order::query()->case([
            when('type_id', 1)->and(['amount' => 1, 'status' => 'complete'])->or(['amount' => 2, 'status' => 'initiated'])->then('success'),
            when('type_id', 1)->and(['amount' => 1, 'status' => 'complete'])->or(['amount' => 2, 'status' => 'initiated'])->then('success'),
            when('type_id', 1)->and(['amount' => 1, 'status' => 'complete'])->or(['amount' => 2, 'status' => 'initiated'])->then('success')
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? and ( `amount` = ? and `status` = ? ) or ( `amount` = ? or `status` = ? ) then ? when `type_id` = ? and ( `amount` = ? and `status` = ? ) or ( `amount` = ? or `status` = ? ) then ? when `type_id` = ? and ( `amount` = ? and `status` = ? ) or ( `amount` = ? or `status` = ? ) then ? else ? end) as `type` from \"orders\"", $query->toSql());
    }

    public function test_case_with_case_on_then()
    {
        $query = Order::query()->case([
            when('type_id', 1)->then([
                when('name', 'deposit')->then('deposit_slug'),
            ], 'N/A'),
        ], 'N/A', 'type');

        $this->assertSame("select (case when `type_id` = ? then ( case when `name` = ? then ? else ? end ) else ? end) as `type` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 5, [1, 'deposit', 'deposit_slug', 'N/A', 'N/A']);
    }

    public function test_logical_builder()
    {
        $query = Order::query()->case([
            when('type', 'withdrawal')
                ->and(function (LogicalBuilder $query) {
                    $query->and('status', '>', 30)
                        ->or('status', '<', 10);
            })->then('inside range')
        ], 'else', 'result');

        $this->assertSame("select (case when `type` = ? and ( ( `status` > ? ) or ( `status` < ? ) )  then ? else ? end) as `result` from \"orders\"", $query->toSql());
        $this->validateBindings($query->getBindings(), 5, ['withdrawal', 30 , 10, 'inside range', 'else']);
    }
}