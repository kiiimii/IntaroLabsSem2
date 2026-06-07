<?php

namespace BazaraJack\Library\Model;

class Book {

    public static function findAll(): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM books ORDER BY read_date DESC";
        $stmt = $db->query($sql);
        
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch();
        
        return $book ?: null;
    }

    public static function create(array $data): bool {
        $db = Database::getConnection();
        $sql = "INSERT INTO books (title, author, cover_path, file_path, read_date, allow_download) 
                VALUES (:title, :author, :cover_path, :file_path, :read_date, :allow_download)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        
        // 1. Убедись, что список колонок СТРОГО совпадает с тем, что ты передаешь
        $sql = "UPDATE books SET 
                    title = :title, 
                    author = :author, 
                    cover_path = :cover_path, 
                    file_path = :file_path, 
                    read_date = :read_date, 
                    allow_download = :allow_download 
                WHERE id = :id";
        
        $stmt = $db->prepare($sql);

        // 2. Явно передаем массив, чтобы исключить попадание лишнего мусора из $request
        return $stmt->execute([
            'title'           => $data['title'],
            'author'          => $data['author'],
            'cover_path'      => $data['cover_path'],
            'file_path'       => $data['file_path'],
            'read_date'       => $data['read_date'],
            'allow_download' => $data['allow_download'],
            'id'              => $id
        ]);
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM books WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }   
}