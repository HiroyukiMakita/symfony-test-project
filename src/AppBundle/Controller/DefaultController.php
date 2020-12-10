<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render(
            'default/index.html.twig',
            array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
            )
        );
    }

    /**
     * @Route
     * (
     *     "/hello/{name}.{_format}",
     *     defaults={"_format"="html"},
     *     requirements = { "_format" = "html|xml|json" },
     *     name="hello"
     * )
     */
    public function helloAction($name)
    {
        return $this->render(
            'default/hello.html.twig',
            array(
                'name' => $name,
            )
        );
    }

    /**
     * @Route("/form", name="formpage")
     */
    public function formAction(Request $request)
    {
        $product = new Product();
        $product->setName('name1');
        $product->setPrice(100);
        $product->setDescription('description1');

        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('price', NumberType::class)
            ->add('description', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Product'))
            ->getForm();

        /**
         * ユーザーがフォームを送信すると、Product の各プロパティにデータを書き込む。
         */
        $form->handleRequest($request);

        /**
         * isSubmitted() はフォームが送信されたかどうか
         * isValid() は送信前は false フォーム送信時に検証した結果により変わる
         */
        if ($form->isSubmitted() && $form->isValid()) {
            // フォーム送信成功時の処理

            /*
             * DB からのオブジェクトの取得、DB へのオブジェクトの保存
             * を行う Doctrine エンティティマネージャの取得
             */
            $doctrine = $this->getDoctrine()->getManager();
            /*
             * Doctrine エンティティマネージャに $product オブジェクトの操作を指定
             * ※ この段階では DB へのクエリーはされない
             */
            $doctrine->persist($product);

            // 現状のエンティティの変更を DB に反映するクエリ実行（プリペアドステートメント使用）
            $doctrine->flush();

            return $this->redirectToRoute('submitted');
        }

        return $this->render(
            'default/form.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route("/success", name="submitted")
     */
    public function submittedAction(Request $request)
    {
        return $this->render('default/submitted.html.twig');
    }

    /**
     * @Route("/products", name="select-all")
     */
    public function selectProductAction(Request $request)
    {
        // product エンティティ全件取得
        $product = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->findAll();

        // 取得した $product オブジェクトにわたす
        return $this->render('default/selected.html.twig', ['products' => $product]);
    }
}
