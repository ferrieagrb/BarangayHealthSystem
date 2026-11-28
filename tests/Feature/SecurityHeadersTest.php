<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_remove_identifiers_and_restrict_embedding_and_form_targets(): void
    {
        $response = (new SecurityHeaders)->handle(Request::create('/login'), function () {
            return response('<html></html>')->withHeaders([
                'X-Powered-By' => 'PHP/test-version',
                'Server' => 'test-platform',
                'X-Generator' => 'test-framework',
            ]);
        });

        foreach (['X-Powered-By', 'Server', 'X-Generator'] as $header) {
            $this->assertFalse($response->headers->has($header));
        }

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertSame('camera=(self)', $response->headers->get('Permissions-Policy'));
        $policy = $response->headers->get('Content-Security-Policy');
        foreach (["object-src 'none'", "frame-ancestors 'self'", "base-uri 'self'", "form-action 'self'"] as $directive) {
            $this->assertStringContainsString($directive, $policy);
        }
        $this->assertStringContainsString("script-src-attr 'none'", $response->headers->get('Content-Security-Policy-Report-Only'));
        $this->assertStringNotContainsString('script-src', $policy);
        $this->assertSame('<html></html>', $response->getContent());
    }
}
