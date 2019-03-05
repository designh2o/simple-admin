<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 05.03.19
 * Time: 5:53
 */

namespace App\Controllers;


use App\Models\Section;

class SectionController extends Controller
{
	public function index()
	{
		$sections = Section::paginate();
		$this->json([
			'items' => $sections->toArray(),
			'currentPage' => $sections->getCurrentPage(),
			'countPage' => $sections->getCountPage(),
		]);
	}

	public function show($id)
	{
		$section = Section::find($id);
		$this->json($section->toArray());
	}

	public function store()
	{
		try {
			$section = Section::create($this->request->getBody());
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}

		$this->json([
			'success' => true
		]);
	}

	public function update()
	{
		try {
			$sectionId = $this->request->id;
			$values = $this->request->getBody();
			if (!isset($values['is_active'])) {
				$values['is_active'] = false;
			}
			$section = Section::find($sectionId);
			if (!$section) {
				throw new \Exception('invalid id ' . $sectionId);
			}
			$section->update($values);
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}
		$this->json([
			'success' => true
		]);
	}

	public function delete()
	{
		$sectionId = $this->request->id;
		$section = Section::find($sectionId);
		if(!$section){
			$this->json([
				'error' => true,
				'message' => 'invalid id '.$sectionId
			]);
			return;
		}
		try {
			$section->delete();
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}
		$this->json([
			'success' => true
		]);
	}

	public function massDelete()
	{
		$ids = $this->request->ids;
		try {
			foreach ($ids as $id) {
				$section = Section::find($id);
				if (!$section) {
					if (!$section) {
						throw new \Exception('invalid id ' . $id);
					}
				}
				$section->delete();
			}
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}
		$this->json([
			'success' => true
		]);
	}

	public function massUpdate()
	{
		$ids = $this->request->ids;
		$body = $this->request->getBody();
		$formatSections = [];
		foreach ($ids as $key => $id){
			if($body['name'][$key] == ""){
				continue;
			}
			$formatSections[] = [
				'id' => $id,
				'name' => $body['name'][$key],
				'description' => $body['description'][$key],
			];
		}
		try {
			foreach ($formatSections as $formatSection) {
				$section = Section::find($formatSection['id']);
				if (!$section) {
					throw new \Exception('invalid id ' . $formatSection['id']);
				}
				$section->update($formatSection);
			}
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}
		$this->json([
			'success' => true
		]);
	}
}