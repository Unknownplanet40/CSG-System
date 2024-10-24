<?php

require_once  "../../Database/Config.php";

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function response($data)
{
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['error' => 'Invalid request method']);
    }
    
    $postID = $_POST['postID'];
    $UUID = $_POST['UUID'];
    $reaction = $_POST['reaction'];

    $stmt = $conn->prepare("SELECT * FROM userannouncement WHERE postID = ?");
    $stmt->bind_param("s", $postID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        switch ($reaction) {
            case 'like': // like the post
                $stmt = $conn->prepare("UPDATE userannouncement SET postLikes = postLikes + 1 WHERE postID = ?");
                $stmt->bind_param("s", $postID);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE userannouncement SET likeBy = IF(likeBy IS NULL OR likeBy = '', ?, CONCAT(likeBy, ',', ?)) WHERE postID = ?");
                $stmt->bind_param("sss", $UUID, $UUID, $postID);
                $stmt->execute();
                $stmt->close();
                response(['status' => 'success', 'message' => 'Post liked']);
                break;
            case 'dislike': // dislike the post
                $stmt = $conn->prepare("UPDATE userannouncement SET postDislikes = postDislikes + 1 WHERE postID = ?");
                $stmt->bind_param("s", $postID);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE userannouncement SET dislikeBy = IF(dislikeBy IS NULL OR dislikeBy = '', ?, CONCAT(dislikeBy, ',', ?)) WHERE postID = ?");
                $stmt->bind_param("sss", $UUID, $UUID, $postID);
                $stmt->execute();
                $stmt->close();

                response(['status' => 'success', 'message' => 'Post disliked']);
                break;
            case 'like-dislike': // remove like then dislike the post
                $stmt = $conn->prepare("UPDATE userannouncement SET postLikes = postLikes - 1 WHERE postID = ?");
                $stmt->bind_param("s", $postID);
                $stmt->execute();
                $stmt->close();
                
                $stmt = $conn->prepare("UPDATE userannouncement SET likeBy = REPLACE(likeBy, ?, '') WHERE postID = ?");
                $stmt->bind_param("ss", $UUID, $postID);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE userannouncement SET postDislikes = postDislikes + 1 WHERE postID = ?");
                $stmt->bind_param("s", $postID);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE userannouncement SET dislikeBy = IF(dislikeBy IS NULL OR dislikeBy = '', ?, CONCAT(dislikeBy, ',', ?)) WHERE postID = ?");
                $stmt->bind_param("sss", $UUID, $UUID, $postID);
                $stmt->execute();
                $stmt->close();

                response(['status' => 'success', 'message' => 'Post disliked']);
                break;
            case 'dislike-like': // remove dislike then like the post
                $stmt = $conn->prepare("UPDATE userannouncement SET postDislikes = postDislikes - 1 WHERE postID = ?");
                $stmt->bind_param("s", $postID);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE userannouncement SET dislikeBy = REPLACE(dislikeBy, ?, '') WHERE postID = ?");
                $stmt->bind_param("ss", $UUID, $postID);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE userannouncement SET postLikes = postLikes + 1 WHERE postID = ?");
                $stmt->bind_param("s", $postID);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE userannouncement SET likeBy = IF(likeBy IS NULL OR likeBy = '', ?, CONCAT(likeBy, ',', ?)) WHERE postID = ?");
                $stmt->bind_param("sss", $UUID, $UUID, $postID);
                $stmt->execute();
                $stmt->close();

                response(['status' => 'success', 'message' => 'Post liked']);
                break;
            case 'unlike': // remove like from the post
                $stmt = $conn->prepare("UPDATE userannouncement SET postLikes = postLikes - 1 WHERE postID = ?");
                $stmt->bind_param("s", $postID);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE userannouncement SET likeBy = REPLACE(likeBy, ?, '') WHERE postID = ?");
                $stmt->bind_param("ss", $UUID, $postID);
                $stmt->execute();
                $stmt->close();

                response(['status' => 'success', 'message' => 'Post unliked']);
                break;
            case 'undislike': // remove dislike from the post
                $stmt = $conn->prepare("UPDATE userannouncement SET postDislikes = postDislikes - 1 WHERE postID = ?");
                $stmt->bind_param("s", $postID);
                $stmt->execute();
                $stmt->close();
                
                $stmt = $conn->prepare("UPDATE userannouncement SET dislikeBy = REPLACE(dislikeBy, ?, '') WHERE postID = ?");
                $stmt->bind_param("ss", $UUID, $postID);
                $stmt->execute();
                $stmt->close();

                response(['status' => 'success', 'message' => 'Post undisliked']);
                break;
            default:
                response(['status' => 'error', 'message' => 'Invalid reaction']);
                break;
        }
    } else {
        response(['status' => 'error', 'message' => 'Post not found']);
    }

} catch (Exception $e) {
    response(['status' => 'error', 'message' => 'Something went wrong while posting reaction to the post - ' . $e->getMessage()]);
}
