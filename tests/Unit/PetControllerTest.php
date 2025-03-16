<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\PetController;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Services\PetDataProcessor;
use App\Services\PetstoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Tests\TestCase;
use Mockery;
use Illuminate\Http\RedirectResponse;

class PetControllerTest extends TestCase
{
    protected $petstoreServiceMock;
    protected $petDataProcessorMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petstoreServiceMock = Mockery::mock(PetstoreService::class);
        $this->petDataProcessorMock = Mockery::mock(PetDataProcessor::class);

        $this->controller = new PetController(
            $this->petstoreServiceMock,
            $this->petDataProcessorMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndexReturnsCorrectViewWithPets()
    {
        $petsCollection = collect([
            ['id' => 1, 'name' => 'Rex', 'status' => 'available'],
            ['id' => 2, 'name' => 'Fluffy', 'status' => 'pending']
        ]);

        $paginatedPets = new LengthAwarePaginator(
            $petsCollection,
            $petsCollection->count(),
            PetController::ITEMS_PER_PAGE,
            1
        );

        // Konfiguracja mocków
        $this->petstoreServiceMock
            ->shouldReceive('searchPets')
            ->once()
            ->with(null, 'asc')
            ->andReturn($petsCollection);

        $this->petstoreServiceMock
            ->shouldReceive('paginatePets')
            ->once()
            ->with($petsCollection, 1, PetController::ITEMS_PER_PAGE, Mockery::any())
            ->andReturn($paginatedPets);

        // Wykonanie metody kontrolera
        $request = Request::create('/pets', 'GET');
        $response = $this->controller->index($request);

        // Asercje
        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('pets.index', $response->getName());
        $this->assertArrayHasKey('pets', $response->getData());
        $this->assertInstanceOf(LengthAwarePaginator::class, $response->getData()['pets']);
        $this->assertEquals($paginatedPets, $response->getData()['pets']);
    }

    public function testIndexHandlesApiError()
    {
        // Konfiguracja mocków
        $this->petstoreServiceMock
            ->shouldReceive('searchPets')
            ->once()
            ->with(null, 'asc')
            ->andReturn(null);

        // Wykonanie metody kontrolera
        $request = Request::create('/pets', 'GET');
        $response = $this->controller->index($request);

        // Asercje
        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('pets.index', $response->getName());
        $this->assertArrayHasKey('error', $response->getData());
        $this->assertInstanceOf(LengthAwarePaginator::class, $response->getData()['pets']);
        $this->assertEquals(0, $response->getData()['pets']->total());
    }

    public function testShowDisplaysPetDetails()
    {
        // Przygotowanie danych testowych
        $pet = [
            'id' => 1,
            'name' => 'Rex',
            'status' => 'available'
        ];

        // Konfiguracja mocków
        $this->petstoreServiceMock
            ->shouldReceive('getPet')
            ->once()
            ->with(1)
            ->andReturn($pet);

        // Wykonanie metody kontrolera
        $response = $this->controller->show(1);

        // Asercje
        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('pets.show', $response->getName());
        $this->assertArrayHasKey('pet', $response->getData());
        $this->assertEquals($pet, $response->getData()['pet']);
    }

    public function testShowRedirectsWhenPetNotFound()
    {
        // Konfiguracja mocków
        $this->petstoreServiceMock
            ->shouldReceive('getPet')
            ->once()
            ->with(999)
            ->andReturn(null);

        // Wykonanie metody kontrolera
        $response = $this->controller->show(999);

        // Asercje
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('pets.index'), $response->getTargetUrl());
    }

    public function testCreateReturnsCorrectView()
    {
        $response = $this->controller->create();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('pets.create', $response->getName());
    }

    public function testStoreCreatesPetSuccessfully()
    {
        // Przygotowanie danych testowych
        $requestData = [
            'name' => 'Rex',
            'status' => 'available'
        ];

        $processedData = [
            'name' => 'Rex',
            'status' => 'available',
            'photoUrls' => []
        ];

        $createdPet = [
            'id' => 1,
            'name' => 'Rex',
            'status' => 'available'
        ];

        // Konfiguracja mocków przy użyciu Mockery
        $requestMock = Mockery::mock(StorePetRequest::class);
        $requestMock->shouldReceive('validated')
            ->once()
            ->andReturn($requestData);

        $this->petDataProcessorMock
            ->shouldReceive('process')
            ->once()
            ->with($requestData)
            ->andReturn($processedData);

        $this->petstoreServiceMock
            ->shouldReceive('createPet')
            ->once()
            ->with($processedData)
            ->andReturn($createdPet);

        // Mockowanie Log::info
        Log::shouldReceive('info')
            ->once()
            ->with('Utworzono nowe zwierzę', ['id' => 1]);

        // Wykonanie metody kontrolera
        $response = $this->controller->store($requestMock);

        // Asercje
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('pets.show', 1), $response->getTargetUrl());
    }

    public function testStoreHandlesCreationFailure()
    {
        // Przygotowanie danych testowych
        $requestData = [
            'name' => 'Rex',
            'status' => 'available'
        ];

        $processedData = [
            'name' => 'Rex',
            'status' => 'available',
            'photoUrls' => []
        ];

        // Konfiguracja mocków przy użyciu Mockery
        $requestMock = Mockery::mock(StorePetRequest::class);
        $requestMock->shouldReceive('validated')
            ->once()
            ->andReturn($requestData);

        // Symulujemy, że requestMock ma także zachowanie klasy Request
        $requestMock->shouldReceive('input')
            ->zeroOrMoreTimes()
            ->andReturn($requestData);

        $this->petDataProcessorMock
            ->shouldReceive('process')
            ->once()
            ->with($requestData)
            ->andReturn($processedData);

        $this->petstoreServiceMock
            ->shouldReceive('createPet')
            ->once()
            ->with($processedData)
            ->andReturn(null);

        // Wykonanie metody kontrolera
        $response = $this->controller->store($requestMock);

        // Asercje
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('http://', $response->getTargetUrl());
    }

    public function testEditDisplaysFormWithPetData()
    {
        $pet = [
            'id' => 1,
            'name' => 'Rex',
            'status' => 'available'
        ];

        $this->petstoreServiceMock
            ->shouldReceive('getPet')
            ->once()
            ->with(1)
            ->andReturn($pet);

        $response = $this->controller->edit(1);

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('pets.edit', $response->getName());
        $this->assertArrayHasKey('pet', $response->getData());
        $this->assertEquals($pet, $response->getData()['pet']);
    }

    public function testEditRedirectsWhenPetNotFound()
    {
        $this->petstoreServiceMock
            ->shouldReceive('getPet')
            ->once()
            ->with(999)
            ->andReturn(null);

        $response = $this->controller->edit(999);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('pets.index'), $response->getTargetUrl());
    }

    public function testUpdateModifiesPetSuccessfully()
    {
        $requestData = [
            'name' => 'Rex Updated',
            'status' => 'sold'
        ];

        $processedData = [
            'id' => 1,
            'name' => 'Rex Updated',
            'status' => 'sold'
        ];

        $updatedPet = [
            'id' => 1,
            'name' => 'Rex Updated',
            'status' => 'sold'
        ];

        $requestMock = Mockery::mock(UpdatePetRequest::class);
        $requestMock->shouldReceive('validated')
            ->once()
            ->andReturn($requestData);

        $this->petDataProcessorMock
            ->shouldReceive('process')
            ->once()
            ->with($requestData, 1)
            ->andReturn($processedData);

        $this->petstoreServiceMock
            ->shouldReceive('updatePet')
            ->once()
            ->with($processedData)
            ->andReturn($updatedPet);

        $response = $this->controller->update($requestMock, 1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('pets.show', 1), $response->getTargetUrl());
    }

    public function testUpdateHandlesUpdateFailure()
    {
        $requestData = [
            'name' => 'Rex Updated',
            'status' => 'sold'
        ];

        $processedData = [
            'id' => 1,
            'name' => 'Rex Updated',
            'status' => 'sold'
        ];

        $requestMock = Mockery::mock(UpdatePetRequest::class);
        $requestMock->shouldReceive('validated')
            ->once()
            ->andReturn($requestData);

        $requestMock->shouldReceive('input')
            ->zeroOrMoreTimes()
            ->andReturn($requestData);

        $this->petDataProcessorMock
            ->shouldReceive('process')
            ->once()
            ->with($requestData, 1)
            ->andReturn($processedData);

        $this->petstoreServiceMock
            ->shouldReceive('updatePet')
            ->once()
            ->with($processedData)
            ->andReturn(null);

        $response = $this->controller->update($requestMock, 1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('http://', $response->getTargetUrl());
    }

    public function testDestroyRemovesPetSuccessfully()
    {
        $this->petstoreServiceMock
            ->shouldReceive('deletePet')
            ->once()
            ->with(1)
            ->andReturn(true);

        $response = $this->controller->destroy(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('pets.index'), $response->getTargetUrl());
    }

    public function testDestroyHandlesDeletionFailure()
    {
        $this->petstoreServiceMock
            ->shouldReceive('deletePet')
            ->once()
            ->with(999)
            ->andReturn(false);

        $response = $this->controller->destroy(999);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
