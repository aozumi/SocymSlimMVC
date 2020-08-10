<?php
use \SocymSlim\MVC\controllers\MemberController;

$app->get("/goMemberAdd", MemberController::class . ":goMemberAdd");
$app->post("/memberAdd", MemberController::class . ":memberAdd");
