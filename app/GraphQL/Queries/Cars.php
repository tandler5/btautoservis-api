<?php

namespace App\GraphQL\Queries;

use App\Models\Car;
use App\Models\CarCustomer;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Cars
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user;

        $carTable = (new Car)->getTable();
        $carCustomerTable = (new CarCustomer)->getTable();

        $cars = Car::join("{$carCustomerTable}", "{$carTable}.id", '=', "{$carCustomerTable}.car")
            ->where("{$carCustomerTable}.customer", $user->id)
            ->whereNull("{$carCustomerTable}.deleted_at")
            ->select("{$carTable}.*")
            ->get();

        return $cars;
    }
}
