<?php

namespace App\Controller\Admin\Superadmin;

use App\Entity\Video;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/su")
 */

class CategoriesController extends AbstractController
{
   //this categories accessible by only super admin that is su
  /**
   * @Route("/su/categories", name="categories", methods={"GET", "POST"})
   */
  public function categories(CategoryTreeAdminList $categories, Request $request)
  {
      $categories->getCategoryList($categories->buildTree());
      $category = new Category();
      $form = $this->createForm(CategoryType::class, $category);
      $is_invalid = null;
    

      if($this->saveCategory($category, $form, $request))
      {
          return $this->redirectToRoute('categories');
      }
      elseif($request->isMethod('post'))
      {
          $is_invalid = ' is-invalid';
      }

      return $this->render('admin/categories.html.twig',[
          'categories'=>$categories->categorylist,
          'form' => $form->createView(),
          'is_invalid' => $is_invalid
      ]);
  }

  /**
     * @Route("/su/edit-category/{id}", name="edit_category", methods={"GET", "POST"})
     */
    public function editCategory(Category $category, Request $request)
    {
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;      

        if($this->saveCategory($category, $form, $request))
        {
            return $this->redirectToRoute('categories');
        }
        elseif($request->isMethod('post'))
        {
            $is_invalid = ' is-invalid';
        }
        return $this->render('admin/edit_category.html.twig',[

            'category' => $category,
            'form' => $form->createView(),
            'is_invalid' => $is_invalid
        ]);
    }

    /**
     * @Route("/su/delete-category/{id}", name="delete_category")
     */
    public function deleteCategory(Category $category)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();
        return $this->redirectToRoute('categories');
    }


     //create and edit
    private function saveCategory($category, $form, $request)
    {
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            
           $category->setName($request->request->get('category')['name']);//get the category name from the formulaire by post method
            $repository = $this->getDoctrine()->getRepository(Category::class);
            $parent =  $repository->find($request->request->get('category')['parent']);
            $category->setParent($parent);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return true;

        }
        return false;
    }

    //the following code we insert into the edit_category.html.twig with render contoller function 
    //see the file "edit_category.html.twig" line 15

    public function getAllCategories(CategoryTreeAdminOptionList $categories,  $editedCategory = null)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $categories->getCategoryList($categories->buildTree());
        return $this->render('admin/_all_categories.html.twig', [
            'categories' => $categories,
            'editedCategory' => $editedCategory
        ]);
    }
}
