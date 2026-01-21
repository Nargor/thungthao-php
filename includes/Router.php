<?php
namespace App\includes;
use App\includes\Layout;
use App\includes\Database;
require_once(__DIR__ . '/../vendor/autoload.php');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoreFunction.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Layout.php';

class Router
{
    private $basePath;
    private $layout;
    private $db;
    private $params = [];
    private $redirects = [
        'courses' => 'course'
    ];

    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;
        $this->layout = Layout::getInstance();
        $this->db = Database::getInstance();
    }

    public function dispatch()
    {
        $request = $_SERVER['REQUEST_URI'];
        $request = str_replace($this->basePath, '', $request);


        $parsedUrl = parse_url($request);
        $path = trim($parsedUrl['path'], '/');

        if (empty($path)) {
            $path = 'index';
        }


        if (isset($this->redirects[$path])) {
            header('Location: /' . $this->redirects[$path]);
            exit;
        }


        $matchedPath = $this->findMatchingRoute($path);
        if ($matchedPath) {
            return $this->renderPage($matchedPath);
        }


        http_response_code(404);
        return $this->renderPage(__DIR__ . '/../pages/404.php');
    }

    private function findMatchingRoute($request)
    {
        $segments = $request ? explode('/', $request) : ['index'];
        $currentPath = __DIR__ . '/../pages';
        $paramValues = [];


        if (count($segments) === 1 && $segments[0] === 'index') {
            $indexFile = $currentPath . '/index.php';
            return file_exists($indexFile) ? $indexFile : false;
        }


        $paramPattern = '/^\[(.*?)\]$/';
        $segmentCount = count($segments);

        for ($i = 0; $i < $segmentCount; $i++) {
            $segment = $segments[$i];
            $found = false;
            $isLast = ($i === $segmentCount - 1);


            if ($isLast) {

                $filePath = $currentPath . '/' . $segment . '.php';
                if (file_exists($filePath)) {
                    return $filePath;
                }


                $indexPath = $currentPath . '/' . $segment . '/index.php';
                if (file_exists($indexPath)) {
                    return $indexPath;
                }
            }


            $dirPath = $currentPath . '/' . $segment;
            if (is_dir($dirPath)) {
                $currentPath = $dirPath;
                continue;
            }


            $items = scandir($currentPath);
            foreach ($items as $item) {

                $itemName = pathinfo($item, PATHINFO_FILENAME);


                if (preg_match($paramPattern, $itemName, $matches)) {
                    $paramName = $matches[1];


                    $this->params[$paramName] = $segment;


                    if ($isLast && substr($item, -4) === '.php') {
                        return $currentPath . '/' . $item;
                    }


                    $paramPath = $currentPath . '/' . $item;
                    if (!$isLast && is_dir($paramPath)) {
                        $currentPath = $paramPath;
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                return false;
            }
        }


        $indexFile = $currentPath . '/index.php';
        return file_exists($indexFile) ? $indexFile : false;
    }

    private function renderPage($filePath)
    {

        if ($this->isProtectedRoute($filePath) && !isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        ob_start();


        $db = $this->db;
        $layout = $this->layout;


        $setHead = function ($headContent) use ($layout) {
            $layout->setHead($headContent);
        };


        function useParams()
        {
            global $router;
            return $router->getParams();
        }

        include $filePath;

        if (ob_get_level()) {
            $content = ob_get_clean();
            $this->layout->setContent($content);
            echo $this->layout->render();
        }
    }

    public function getParams()
    {
        return $this->params;
    }

    private function isProtectedRoute($filePath)
    {
        $protectedPaths = ['dashboard', 'admin'];
        $relativePath = str_replace(__DIR__ . '/../../pages/', '', $filePath);
        $firstSegment = explode('/', $relativePath)[0];
        return in_array($firstSegment, $protectedPaths);
    }
}