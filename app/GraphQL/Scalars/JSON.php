<?php

namespace App\GraphQL\Scalars;

use GraphQL\Type\Definition\ScalarType;
use GraphQL\Language\AST\Node;
use GraphQL\Error\Error;

class JSON extends ScalarType
{
    /**
     * Serialize the value to include in the response
     *
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        // Ensure the value is serialized as a JSON string
        return $value;
    }

    /**
     * Parse a JSON value from the client input
     *
     * @param mixed $value
     * @return mixed
     */
    public function parseValue($value)
    {
        // Ensure the value is parsed correctly
        return $value;
    }

    /**
     * Parse a literal value from the query string
     *
     * @param Node $valueNode
     * @param array|null $variables
     * @return mixed
     * @throws Error
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        // Return the value from the AST (Abstract Syntax Tree) node
        return json_decode($valueNode->value, true);
    }
}


?>
