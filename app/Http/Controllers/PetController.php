<?php

namespace App\Http\Controllers;

use App\Services\PetstoreService;
use App\Services\PetDataProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;

class PetController extends Controller
{
    protected PetstoreService $petstoreService;
    protected PetDataProcessor $petDataProcessor;
    public const ITEMS_PER_PAGE = 30;


    public function __construct(PetstoreService $petstoreService, PetDataProcessor $petDataProcessor)
    {
        $this->petstoreService = $petstoreService;
        $this->petDataProcessor = $petDataProcessor;
        $this->middleware('api.errors');
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        try {
            $petsCollection = $this->petstoreService->searchPets(
                $request->get('search_id'),
                $request->get('direction', 'asc')
            );

            if ($petsCollection === null) {
                $pets = new LengthAwarePaginator(
                    collect([]),
                    0,
                    self::ITEMS_PER_PAGE,
                    1,
                    ['path' => $request->url()]
                );

                return view('pets.index', [
                    'pets' => $pets,
                    'direction' => $request->get('direction', 'asc'),
                    'error' => 'Nie udało się pobrać danych z API Petstore. Serwer może być niedostępny lub wystąpił błąd komunikacji.'
                ]);
            }

            $pets = $this->petstoreService->paginatePets(
                $petsCollection,
                (int) $request->get('page', 1),
                self::ITEMS_PER_PAGE,
                $request->url()
            );

            return view('pets.index', [
                'pets' => $pets,
                'direction' => $request->get('direction', 'asc')
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd pobierania zwierząt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_params' => $request->all()
            ]);

            $pets = new LengthAwarePaginator(
                collect([]),
                0,
                self::ITEMS_PER_PAGE,
                1,
                ['path' => $request->url()]
            );

            return view('pets.index', [
                'pets' => $pets,
                'direction' => $request->get('direction', 'asc'),
                'error' => 'Wystąpił nieoczekiwany błąd podczas pobierania danych. Szczegóły zostały zapisane w logach. Proszę spróbować ponownie później.'
            ]);
        }
    }


    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function show(int $id): View|RedirectResponse
    {
        try {
            $pet = $this->petstoreService->getPet($id);
            if (!$pet) {
                return redirect()->route('pets.index')->with('error', 'Nie znaleziono zwierzęcia o podanym ID.');
            }
            return view('pets.show', compact('pet'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas wyświetlania zwierzęcia', ['error' => $e->getMessage()]);
            return redirect()->route('pets.index')->with('error', 'Wystąpił błąd podczas pobierania szczegółów zwierzęcia.');
        }
    }

    /**
     * @return View
     */
    public function create(): View
    {
        return view('pets.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(StorePetRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $petData = $this->petDataProcessor->process($validatedData);
            $pet = $this->petstoreService->createPet($petData);

            if ($pet !== null) {
                Log::info('Utworzono nowe zwierzę', ['id' => $pet['id']]);
                return redirect()->route('pets.show', $pet['id'])
                    ->with('success', "Zwierzę zostało utworzone pomyślnie. ID: {$pet['id']}");
            }

            return redirect()->back()->withInput()
                ->with('error', 'Nie udało się utworzyć zwierzęcia. Spróbuj ponownie.');
        } catch (\Exception $e) {
            Log::error('Błąd tworzenia zwierzęcia', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()
                ->with('error', 'Wystąpił błąd podczas tworzenia zwierzęcia.');
        }
    }

    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $pet = $this->petstoreService->getPet($id);

            if ($pet === null) {
                return redirect()->route('pets.index')->with('error', 'Nie znaleziono zwierzęcia o podanym ID.');
            }

            return view('pets.edit', compact('pet'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas edycji zwierzęcia', ['error' => $e->getMessage()]);
            return redirect()->route('pets.index')->with('error', 'Wystąpił błąd podczas edycji zwierzęcia.');
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdatePetRequest $request, int $id): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $petData = $this->petDataProcessor->process($validatedData, $id);
            $pet = $this->petstoreService->updatePet($petData);

            if ($pet === null) {
                return redirect()->back()->withInput()->with('error', 'Nie udało się zaktualizować zwierzęcia. Spróbuj ponownie.');
            }

            return redirect()->route('pets.show', $id)->with('success', 'Zwierzę zostało zaktualizowane pomyślnie.');
        } catch (\Exception $e) {
            Log::error('Błąd aktualizacji zwierzęcia', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Wystąpił błąd podczas aktualizacji zwierzęcia.');
        }
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $result = $this->petstoreService->deletePet($id);

            if (!$result) {
                return redirect()->back()->with('error', 'Nie udało się usunąć zwierzęcia. Spróbuj ponownie.');
            }

            return redirect()->route('pets.index')->with('success', 'Zwierzę zostało usunięte pomyślnie.');
        } catch (\Exception $e) {
            Log::error('Błąd usuwania zwierzęcia', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Wystąpił błąd podczas usuwania zwierzęcia.');
        }
    }
}
