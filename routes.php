<?php
use \SocymSlim\MVC\controllers\MemberController;

$app->get("/goMemberAdd", MemberController::class . ":goMemberAdd");
$app->post("/memberAdd", MemberController::class . ":memberAdd");
$app->get("/showMemberDetail/{id}", MemberController::class . ":showMemberDetail");
