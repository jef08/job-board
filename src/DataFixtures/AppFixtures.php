<?php

namespace App\DataFixtures;

use App\Entity\Application;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Freelancer;
use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ApplicationStatus;
use App\Enum\ListingStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker\Generator;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private SluggerInterface $slugger,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $categories = $this->createCategories($manager);
        $companies = $this->createCompanies($manager, $faker, 10);
        $listings = $this->createListings($manager, $faker, $companies, $categories);
        $freelancers = $this->createFreelancers($manager, $faker, 10, $categories);
        $this->createApplications($manager, $faker, $freelancers, $listings);

        $manager->flush();
    }

    private function createCategories(ObjectManager $manager): array
    {
        $categoryData = [
            'Web Development' => 'bi-code-slash',
            'Mobile Development' => 'bi-phone',
            'Design & Creative' => 'bi-palette',
            'Writing & Translation' => 'bi-pencil-square',
            'Marketing & Sales' => 'bi-megaphone',
            'Data & Analytics' => 'bi-bar-chart',
            'IT & Networking' => 'bi-hdd-network',
            'Admin & Customer Support' => 'bi-headset',
            'Video & Animation' => 'bi-camera-reels',
            'Business & Finance' => 'bi-briefcase',
        ];

        $categories = [];

        foreach ($categoryData as $name => $icon) {
            $category = new Category();
            $category->setName($name);
            $category->setSlug((string) $this->slugger->slug($name)->lower());
            $category->setIcon($icon);

            $manager->persist($category);
            $categories[] = $category;
        }

        return $categories;
    }

    private function createCompanies(ObjectManager $manager, Generator $faker, int $count): array
    {
        $companies = [];

        for ($i = 0; $i < $count; $i++) {
            $companyName = $faker->company();

            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setName($companyName);
            $user->setLocation($faker->city());
            $user->setBio($faker->catchPhrase());
            $user->setRoles(['ROLE_COMPANY']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

            $company = new Company();
            $company->setUser($user);
            $company->setIndustry($faker->randomElement(['Software', 'Marketing', 'Design', 'Finance', 'Healthcare']));
            $company->setWebsite($faker->url());
            $company->setCompanySize($faker->numberBetween(5, 500));

            $manager->persist($user);
            $manager->persist($company);
            $companies[] = $company;
        }

        return $companies;
    }

    private function createListings(ObjectManager $manager, Generator $faker, array $companies, array $categories): array
    {
        $listings = [];
        $companyIndex = 0;

        foreach ($companies as $company) {
            for ($j = 0; $j < 3; $j++) {
                $title = $faker->jobTitle();

                $listing = new Listing();
                $listing->setTitle($title);
                $listing->setDescription($faker->paragraphs(3, true));
                $listing->setJobType($faker->randomElement(['Full-time', 'Part-time', 'Freelance', 'Remote']));
                $listing->setSalaryRange('$' . $faker->numberBetween(30, 60) . '-' . $faker->numberBetween(61, 120) . '/hr');
                $listing->setCategory($faker->randomElement($categories));
                $listing->setCompany($company);

                // Randomly close ~20% of listings so the dashboard/browse pages show a mix
                if ($faker->boolean(20)) {
                    $listing->setStatus(ListingStatus::Closed);
                }

                // Append indices to guarantee a unique slug across the whole fixture run
                // without needing to query the DB — real controller code uses the
                // DB-backed uniqueness check we built earlier, this is a fixtures-only shortcut.
                $baseSlug = (string) $this->slugger->slug($title)->lower();
                $listing->setSlug($baseSlug . '-' . $companyIndex . '-' . $j);

                $manager->persist($listing);
                $listings[] = $listing;
            }
            $companyIndex++;
        }

        return $listings;
    }

    private function createFreelancers(ObjectManager $manager, Generator $faker, int $count, array $categories): array
    {
        $freelancers = [];

        for ($i = 0; $i < $count; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setName($faker->name());
            $user->setLocation($faker->city());
            $user->setBio($faker->paragraph());
            $user->setRoles(['ROLE_FREELANCER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

            $freelancer = new Freelancer();
            $freelancer->setUser($user);
            $freelancer->setHourlyRate($faker->randomFloat(2, 20, 150));
            $freelancer->setYearsExperience($faker->numberBetween(0, 20));
            $freelancer->setCategory($faker->randomElement($categories));

            $manager->persist($user);
            $manager->persist($freelancer);
            $freelancers[] = $freelancer;
        }

        return $freelancers;
    }

    private function createApplications(ObjectManager $manager, Generator $faker, array $freelancers, array $listings): void
    {
        foreach ($freelancers as $freelancer) {
            // array_rand with count > 1 returns distinct keys, so this guarantees
            // a freelancer never "applies" to the same listing twice
            $chosenKeys = array_rand($listings, min(5, count($listings)));

            foreach ((array) $chosenKeys as $key) {
                $application = new Application();
                $application->setFreelancer($freelancer);
                $application->setListing($listings[$key]);
                $application->setCoverMessage($faker->paragraph());
                $application->setStatus($faker->randomElement([
                    ApplicationStatus::Pending,
                    ApplicationStatus::Accepted,
                    ApplicationStatus::Rejected,
                ]));

                $manager->persist($application);
            }
        }
    }
}