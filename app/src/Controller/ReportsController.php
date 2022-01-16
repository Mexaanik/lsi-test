<?php

namespace App\Controller;

use App\Entity\Reports;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;

class ReportsController extends AbstractController
{
    /**
     * @Route("/", name="reports")
     */
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $params = $request->request->all();

        $repository = $doctrine->getRepository(Reports::class);
        if (!empty($params)) {
            $qb = $doctrine->getManager()->createQueryBuilder();
            $qb->select('r')
                ->from(Reports::class, 'r')
                ->where('r.range = :local and r.datetime BETWEEN :from AND :to');

            if (!empty($params['local'])){
                $qb->setParameter('local', $params['local']);
            }
            if (!empty($params['start']) && !empty($params['end'])){
                $qb->setParameter('from', $params['start'])
                    ->setParameter('to', $params['end']);
            }
            $reports = $qb->getQuery()->getResult();
        } else {
            $reports = $repository->findAll();
        }
        $reportsArr = [];
        $i = 0;
        foreach ($reports as $report) {
            $reportsArr[$i]['name'] = $report->getName();
            $reportsArr[$i]['date'] = $report->getDatetime()->format('Y-m-d');
            $reportsArr[$i]['time'] = $report->getDatetime()->format('H:i');
            $reportsArr[$i]['user'] = $report->getUserName();
            $reportsArr[$i]['range'] = $report->getRange();
            $i++;
        }

        return $this->render('base.html.twig', [
            'reports' => $reportsArr
        ]);
    }
}
