<?php

namespace App\Http\Middleware;

use App\Support\InputCapitalization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CapitalizeTextInputs
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe()) {
            $input = $request->isJson() ? $request->json() : $request->request;
            $input->replace($this->normalize($input->all()));
        }

        return $next($request);
    }

    private function normalize(array $input): array
    {
        foreach ($input as $field => $value) {
            if (is_array($value)) {
                $input[$field] = $this->normalize($value);
            } elseif (is_string($value)) {
                $input[$field] = InputCapitalization::normalize((string) $field, $value);
            }
        }

        return $input;
    }
}
