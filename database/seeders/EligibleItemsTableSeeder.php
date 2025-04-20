<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\EligibleItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EligibleItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countCreated = 0;
        $countSkipped = 0;

        // Get the data to seed
        $eligibleItemsData = $this->getEligibleItemsData();

        try {
            DB::beginTransaction();

            foreach ($eligibleItemsData as $item) {
                // Find product by name
                $product = Product::where('name', $item['product'])->first();
                
                if (!$product) {
                    DB::rollBack();
                    throw new \Exception("Product not found: {$item['product']}");
                }

                // Check if combination already exists
                $exists = EligibleItem::where('product_id', $product->id)
                    ->where('facility_type', 'Primary Health Unit')
                    ->exists();

                if ($exists) {
                    $countSkipped++;
                    $this->command->info("Skipped: {$item['product']} for Primary Health Unit");
                    continue;
                }

                // Create new eligible item
                EligibleItem::create([
                    'product_id' => $product->id,
                    'facility_type' => 'Primary Health Unit'
                ]);

                $countCreated++;
                $this->command->info("Created: {$item['product']} for Primary Health Unit");
            }

            DB::commit();
            $this->command->info("Created {$countCreated} new eligible items.");
            $this->command->info("Skipped {$countSkipped} existing items.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Failed: " . $e->getMessage());
            $this->command->error("All changes have been rolled back.");
            throw $e;
        }
    }

    /**
     * Get the eligible items data to seed
     */
    private function getEligibleItemsData(): array
    {
        return [
            ['product' => 'Albendazole 400mg tablet PAC-100 [Rx of parasitic worms]'],
            ['product' => 'Aluminium hydroxide500 mg tablet [Rx heartburn, acid indigestion]'],
            ['product' => 'Amoxicillin 500 mg + clavulanic acid 125 mg film-coated tablet (blister) [Rx of bacterial infections]'],
            ['product' => 'Amoxycillin Powder for oral suspension, 125mg/5ml BOT-100ml [Rx of pneumonia & otitis media]'],
            ['product' => 'Amoxycillin tablet250 mg tablet PAC-1000 [Rx of pneumonia & otitis media]'],
            ['product' => 'Ascorbic acid50 mg tablet [Rx concurrent infections and malnutrition]'],
            ['product' => 'Benzoic acid and salicylic acidBenzoic acid 6% and salicylic acid 3% ointment [Rx of fungal rash]'],
            ['product' => 'Benzyl benzoate 25% ointment [Rx of scabies]'],
            ['product' => 'Ceftrioxone 1gm inj vial [Rx of bacterial infections]'],
            ['product' => 'Ceftrioxone 250mg Inj [Rx of bacterial infections]'],
            ['product' => 'Chlorhexidine5% solution for dilution 1000ml [disinfection and antisepsis – must be diluted]'],
            ['product' => 'Chlorphenamine2mg/5ml syrup 60ml [Rx of itch and allergy]'],
            ['product' => 'Chlorpheniramine4 mg tablet [Rx allergy symptoms]'],
            ['product' => 'Ciporfloxacin tab 500mg tab[Rx of bacterial infection including UTI]'],
            ['product' => 'Clotrimazole 1% ointment [Rx of fungal rash, including nappy rash in infants]'],
            ['product' => 'Dextrose 5% injection, 500 ml'],
            ['product' => 'Erythromycin ethylsuccinate 125 mg/5 ml powder for oral suspension 100 ml bottle [Rx of bacterial infections]'],
            ['product' => 'Erythromycin250 mg tabs PAC-100 [Rx pneumonia in penicillin allergic individuals; broad spectrum antibiotic]'],
            ['product' => 'Ferrous salt + folic acid (iron & folate)60mg ferrous sulphate tablet + 0.40 mg folic acid PAC 100 [Rx anaemia]'],
            ['product' => 'Hydrocortisone 1% cream TBE-30g [Rx of dermatitis]'],
            ['product' => 'Ibuprofen  100mg/5ml 100ml syrup [Rx of fever, pain and inflammation]'],
            ['product' => 'Ibuprofen  400mg tabulet PAC-100 [Rx of fever, pain and inflammation]'],
            ['product' => 'Magnesium trisilicate Tablets 1000 [antacid for gastritis]'],
            ['product' => 'Methyldopa 500mg tabs PAC-100 [Rx high BP in pregnancy]'],
            ['product' => 'Metronidazole250 mg PAC-100 [Rx of protozoal diarrhoea and pelvic inflammatory disease and gum infections]'],
            ['product' => 'Miconazole nitrate2% TBE-30g [Rx of fungal skin problems]'],
            ['product' => 'Nitrofurantoin 100mg PAC-100 [Rx of UTI]'],
            ['product' => 'Nystatin 100,000 IU lozengeNystatin pessary,[Rx vaginal thrush]'],
            ['product' => 'Nystatin 100,000 IU/ml oral suspension Bot-30mls [Rx of oral thrush in babies]'],
            ['product' => 'Oral Rehydration Salt (ORS)Low osmolarity formula 1L'],
            ['product' => 'Paracetamol 120 mg/5 ml oral solution 100 ml bottle120mg/5ml oral soln 100ml  [Rx of analgesic]'],
            ['product' => 'Paracetamol100 mg PAC-1000 [Rx of pain & fever]'],
            ['product' => 'Paracetamol500 mg PAC-1000 [Rx of pain & fever]'],
            ['product' => 'Phenoxymethylpenicillin250 mg PAC-1000 [Rx of tonsillitis]'],
            ['product' => 'Polyvidone iodine solution10%, 5 L [antisepsis of wounds]'],
            ['product' => 'Ringer lactate solution500 ml'],
            ['product' => 'Silver sulfadiazine 1% topical cream, 500 g [first aid Rx of burns]'],
            ['product' => 'Tetracycline 1% ointment TBE 5 g [Rx of conjunctivitis]'],
            ['product' => 'Zinc oxide 10% cream 500g [Rx of nappy rash in babies and other rashes]'],
            ['product' => 'Zinc sulphate 20mg tabs (only if new ORS with zinc not available for kits) PAC-100 [Rx of diarrhoea]'],
            ['product' => 'Bandage elastic (crepe)- 10cm x 4m'],
            ['product' => 'Bandage elastic (crepe)- 8cm x 4m'],
            ['product' => 'Compress, guaze 10x10cm PAC 100'],
            ['product' => 'Cotton wool, 500g roll'],
            ['product' => 'Extractor, mucus, 20ml ster, disp'],
            ['product' => 'Face mask, surgical, 3 ply, with foldable noseclip, 2 elastic strings, blue, disposable'],
            ['product' => 'Gloves, latex, examination, medium, disposable, non-sterile'],
            ['product' => 'Sachet tablet plastic PAC-500/ for medicines 8x6cm'],
            ['product' => 'Tape, adhesive, Z.O. 2.5cmx5m'],
            ['product' => 'Tongue depressor, wooden, dispos BOX-500'],
            ['product' => 'Umbilical cord tie, 3mm, non-sterile, 100m']
        ];
    }
}
