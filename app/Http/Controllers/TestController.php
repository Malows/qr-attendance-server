<?php
class TestController {
    public function index() {
        return __("missing.key.that.does.not.exist");
    }
}