<?php


declare(strict_types=1);

namespace App\Model\Repository;

use App\Service\Database\MySQLDB;
use App\Model\Entity\Comment;
use App\Model\Entity\Interfaces\EntityObjectInterface;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;
use Config\DotEnv;

final class CommentRepository implements EntityRepositoryInterface
{
    private MySQLDB $database;

    public function __construct(MySQLDB $database)
    {
        (new DotEnv(__DIR__ . '/../../../.env'))->load();
        $this->database = new MySQLDB(getenv('DATABASE_HOST'), getenv('DATABASE_NAME'), getenv('DATABASE_USER'), getenv('DATABASE_PASSWORD'));
    }

    public function find(int $id): ?Comment
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Comment
    {
        return $this->findBy($criteria, $orderBy, 1, 1)[0] ?? null;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $criteriaFields = [];
        $orderByFields = [];
        $binds = [];

        foreach ($criteria as $key => $value) {
            $criteriaFields[] = sprintf("%s = :%s", $key, $key);
            $binds[sprintf(":%s", $key)] = $value;
        }

        if (!is_null($orderBy)) {
            foreach ($orderBy as $key=>$value) {
                $orderByFields[] = sprintf("%s %s", $key, $value);
            }
        }

        $criteriaList = implode(' AND ', $criteriaFields);
        $orderByList = implode(', ', $orderByFields);

        $whereClause = 0 !== count($criteriaFields) ? sprintf('WHERE %s', $criteriaList) : '';
        $orderByClause = 0 !== count($orderByFields) ? sprintf(' ORDER BY %s', $orderByList) : '';
        $limitClause = !is_null($limit) ? ' LIMIT ' . $limit : '';
        $offsetClause = !is_null($offset) ? ' OFFSET ' . $offset : '';

        $prepared = $this->database->prepare('SELECT * FROM comments ' . $whereClause . $orderByClause . $limitClause . $offsetClause);

        return $this->database->execute($prepared, $binds, Comment::class);
    }

    public function findAll(): ?array
    {
        $prepared = $this->database->prepare('select * from comments');
        return $this->database->execute($prepared, [], Comment::class);
    }

    public function create(object $comment): bool
    {
        return false ;
    }

    public function update(object $comment): bool
    {
        return false;
    }

    public function delete(object $comment): bool
    {
        return false;
    }
}
