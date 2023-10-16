<?php

namespace App\Tests\Controller;

interface TestFormInterface
{
    /**
     * @dataProvider provideTestFormIsValid
     *
     * @param array<string, string> $values
     */
    public function testFormIsValid(string $url, string $formSubmit, array $values, ?string $email, ?string $redirectUrl): void;

    public function provideTestFormIsValid(): \Generator;

    /**
     * @param array<string, string>                                   $values
     * @param array<int, array<string, string|array<string, string>>> $errors
     *
     * @dataProvider provideTestFormIsNotValid
     */
    public function testFormIsNotValid(string $url, string $route, string $formSubmit, array $values, array $errors, ?string $email, string $alternateSelector = null): void;

    public function provideTestFormIsNotValid(): \Generator;
}
