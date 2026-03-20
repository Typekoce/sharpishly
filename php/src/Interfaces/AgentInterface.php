namespace App\Interfaces;

interface AgentInterface {
    public function ask(string $prompt, string $system = ""): string;
}
