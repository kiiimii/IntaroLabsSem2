<?php
namespace BazaraJack\Library\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use BazaraJack\Library\Model\Book;
use BazaraJack\Library\Core\View;

class BookController {
    
    public function index(Request $request): Response {
        $session = $request->getSession();
        
        $books = Book::findAll();

        $html = View::getTwig()->render('books/index.html.twig', [
            'books'  => $books,
            'isAuth' => $session->has('user'),
            'user'   => $session->get('user')
        ]);

        return new Response($html);
    }

    
    public function showAddForm(Request $request): Response {
        $session = $request->getSession();

        if (!$session->has('user')) {
            return new RedirectResponse('/login');
        }

        $html = View::getTwig()->render('books/add.html.twig', [
            'isAuth' => true,
            'user'   => $session->get('user')
        ]);

        return new Response($html);
    }

    public function add(Request $request) {
        $session = $request->getSession();
        if (!$session->has('user')) {
            return new RedirectResponse('/login');
        }

        $title = $request->request->get('title');
        $author = $request->request->get('author');
        $readDate = $request->request->get('read_date');
        $isDownloadable = $request->request->has('is_downloadable') ? 1 : 0;

        // Получаем объекты загруженных файлов
        $cover = $request->files->get('cover');
        $file = $request->files->get('book_file');

        // Обработка загрузки
        $coverPath = $this->handleFileUpload($cover, 'covers');
        $filePath = $this->handleFileUpload($file, 'files');

        Book::create([
            'title' => $title,
            'author' => $author,
            'read_date' => $readDate,
            'cover_path' => $coverPath,
            'file_path' => $filePath,
            'allow_download' => $isDownloadable
        ]);

        return new RedirectResponse('/books');
    }

    private function handleFileUpload($file, $type) {
        if (!$file) return null;

        $hash = md5(uniqid() . $file->getClientOriginalName());
        
        // Определяем вложенные папки (первые два символа хеша, затем вторые два)
        $dir1 = substr($hash, 0, 2);
        $dir2 = substr($hash, 2, 2);
        
        $relativeDir = "uploads/$type/$dir1/$dir2";
        $absoluteDir = __DIR__ . "/../../public/" . $relativeDir;

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0777, true);
        }

        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $fileName = $hash . '.' . $extension;

        $file->move($absoluteDir, $fileName);

        return $relativeDir . '/' . $fileName;
    }

    public function edit(Request $request): Response {
        $session = $request->getSession();
        if (!$session->has('user')) {
            return new RedirectResponse('/login');
        }

        $id = (int)$request->attributes->get('id');
        $book = Book::find($id);
        $defaultCover = 'uploads/no-cover.png';

        if ($request->isMethod('POST')) {
            $coverPath = $request->request->has('delete_cover') ? $defaultCover : $book['cover_path'];
            
            $newCover = $request->files->get('cover');
            if ($newCover) {
                $coverPath = $this->handleFileUpload($newCover, 'covers');
            }

            $filePath = $request->request->has('delete_file') ? '' : $book['file_path'];
            $newFile = $request->files->get('book_file');
            if ($newFile) {
                $filePath = $this->handleFileUpload($newFile, 'files');
            }

            Book::update($id, [
                'title'          => $request->request->get('title'),
                'author'         => $request->request->get('author'),
                'read_date'      => $request->request->get('read_date'),
                'allow_download' => $request->request->has('allow_download') ? 1 : 0,
                'cover_path'     => $coverPath,
                'file_path'      => $filePath
            ]);

            return new RedirectResponse('/');
        }

            return new Response(View::getTwig()->render('books/edit.html.twig', [
                'book' => $book,
                'isAuth' => true,
                'user' => $session->get('user')
            ]));
    }

    public function delete(Request $request): Response {
        $session = $request->getSession();
        if (!$session->has('user')) {
            return new RedirectResponse('/login');
        }

        $id = (int)$request->attributes->get('id');
        $book = Book::find($id);

        if ($book) {
            $filesToDelete = [$book['cover_path'], $book['file_path']];
            
            foreach ($filesToDelete as $path) {
                if ($path) {
                    $fullPath = __DIR__ . '/../../public/' . $path;
                    if (file_exists($fullPath) && !str_contains($path, 'no-cover.png')) {
                        unlink($fullPath);
                    }
                }
            }

            Book::delete($id);
        }

        return new RedirectResponse('/');
    }
}