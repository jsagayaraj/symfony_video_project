<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Video;
use App\Form\UserType;
use App\Entity\Comment;
use App\Entity\Category;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\Traits\Likes;
use App\Utils\VideoForNoValidSubscription;


class FrontController extends AbstractController
{
    use Likes;
    /**
     * @Route("/", name="main_page")
     */
    public function index()
    {
        return $this->render('front/index.html.twig');
    }


    /**
     * @Route("/video-list/category/{categoryname}-{id}/{page}", defaults={"page": "1"}, name="video_list")
     */
    //public function videoList($id, $page, CategoryTreeFrontPage $categories, Request $request)
    public function videoList($id, $page, CategoryTreeFrontPage $categories, Request $request, VideoForNoValidSubscription $video_no_members)
    {   
        //dump($categories); //it return main categories
        //$subcategories = $categories->buildTree($id);
        //dump($subcategories); //it returns sub categories depends upon the parent categories
        $categories->getCategoryListAndParent($id);
        $ids = $categories->getChildIds($id);
        array_push($ids, $id);
        
        $videos = $this->getDoctrine()->getRepository(Video::class)->findByChildIds($ids, $page, $request->get('sortby'));
        dump($videos);
        return $this->render('front/video_list.html.twig',[
            'subcategories' => $categories,
            'videos' => $videos,
            'video_no_members' => $video_no_members->check()
        ]);
    }

    /**
     * @Route("/video-details/{video}", name="video_details")
     */
    public function videoDetails($video, VideoRepository $repo,  VideoForNoValidSubscription $video_no_members)
    {
        dump($repo->videoDetails($video));
        return $this->render('front/video_details.html.twig', [
            'video' => $repo->videoDetails($video),
            'video_no_members' => $video_no_members->check()
        ]);
    }

    /**
     * @Route("/search-results/{page}", methods={"GET"}, defaults={"page": "1"}, name="search_results")
     */
    //public function searchResults($page, Request $request)
    public function searchResults($page, Request $request, VideoForNoValidSubscription $video_no_members)
    {
        $videos = null;
        $query = null;

        if($query = $request->get('query'))
        {
            $videos = $this->getDoctrine()
            ->getRepository(Video::class)
            ->findByTitle($query, $page, $request->get('sortby'));

            if(!$videos->getItems()) $videos = null;
        }
       
        return $this->render('front/search_results.html.twig',[
            'videos' => $videos,
            'query' => $query,
            'video_no_members' => $video_no_members->check()
        ]);
    }

    //add comments
    /**
     * @Route("/new-comment/{video}", name="new_comment", methods={"POST"})
     */
    public function newComment(Video $video, Request $request)
    {
        //user should connect to add a connect
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        //if comment field is not empty
        if(!empty(trim($request->request->get('comment'))))
        {   
            // create instance of $comment
            $comment = new Comment();
            //set the content field
            $comment->setContent($request->request->get('comment'));
            //set the user 
            $comment->setUser($this->getUser());
            //set the video which belongs to the comment
            $comment->setVideo($video);

            //save the comment
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

        }
        return $this->redirectToRoute('video_details',['video'=>$video->getId()]);
    }

    //likes, dislikes, unlikes
    /**
     * @Route("/video-list/{video}/like", name="like_video", methods={"POST"})
     * @Route("/video-list/{video}/dislike", name="dislike_video", methods={"POST"})
     * @Route("/video-list/{video}/unlike", name="undo_like_video", methods={"POST"})
     * @Route("/video-list/{video}/undodislike", name="undo_dislike_video", methods={"POST"})
     */
    public function toggleLikesAjax(Video $video, Request $request)
    {
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        switch($request->get('_route'))
        {
            case 'like_video':
            $result = $this->likeVideo($video);
            break;
            
            case 'dislike_video':
            $result = $this->dislikeVideo($video);
            break;

            case 'undo_like_video':
            $result = $this->undoLikeVideo($video);
            break;

            case 'undo_dislike_video':
            $result = $this->undoDislikeVideo($video);
            break;
        }

        return $this->json(['action' => $result,'id'=>$video->getId()]);
    }

    // private function likeVideo($video)
    // {  
    //     //find the user id
    //     $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
    //     $user->addLikedVideo($video);
    //     //save the likes with user id
    //     $em = $this->getDoctrine()->getManager();
    //     $em->persist($user);
    //     $em->flush();
    //     return 'liked';
    // }
    // private function dislikeVideo($video)
    // {
    //     //find the user id
    //     $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
    //     $user->addDislikedVideo($video);
    //     //save the likes with user id
    //     $em = $this->getDoctrine()->getManager();
    //     $em->persist($user);
    //     $em->flush();
    //     return 'disliked';
    // }
    // private function undoLikeVideo($video)
    // {  
    //     //find the user id
    //     $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
    //     $user->removeLikedVideo($video);
    //     //save the likes with user id
    //     $em = $this->getDoctrine()->getManager();
    //     $em->persist($user);
    //     $em->flush();
    //     return 'undo liked';
    // }
    // private function undoDislikeVideo($video)
    // {   
    //     //find the user id
    //     $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
    //     $user->removeDislikedVideo($video);
    //     //save the likes with user id
    //     $em = $this->getDoctrine()->getManager();
    //     $em->persist($user);
    //     $em->flush();
    //     return 'undo disliked';
    // }

   


    public function mainCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findBy(['parent'=>null], ['name'=>'ASC']);
        return $this->render('front/_main_categories.html.twig', [
            'categories'=>$categories
        ]);
    }


    
}
