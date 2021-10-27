<?php


class User {

    protected int $id;
    protected string $name;
    protected int $age;

    public static function fromArray(array $data): self {
        $user = new static();
        $user->id = $data['id'] ?? 1;
        $user->name = $data['name'] ?? 'name';
        $user->age = $data['age'] ?? 21;
        return $user;
    }
}

class SuperUser extends User {

}

print_r(User::fromArray([/*...*/]));
print_r(SuperUser::fromArray([/*...*/]));