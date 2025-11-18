<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'create:admin',
    description: 'Crée un compte administrateur'
)]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l’admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $admin = new User();
        $admin->setEmail($email);

        // Nom généré automatiquement
        $generatedName = ucfirst(explode('@', $email)[0]);
        $admin->setName($generatedName);

        // 📌 Correction : phoneNumber obligatoire → on met un numéro par défaut
        $admin->setPhoneNumber("0000000000");

        // Rôle admin
        $admin->setRoles(['ROLE_ADMIN']);

        // Mot de passe hashé
        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setPassword($hashedPassword);

        // Sauvegarde
        $this->em->persist($admin);
        $this->em->flush();

        $output->writeln("<fg=green>✔ Administrateur créé avec succès !</>");
        $output->writeln("Email : $email");
        $output->writeln("Mot de passe : $password");
        $output->writeln("Nom généré : $generatedName");

        return Command::SUCCESS;
    }
}
