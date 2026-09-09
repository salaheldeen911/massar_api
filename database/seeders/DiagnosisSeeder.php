<?php

namespace Database\Seeders;

use App\Models\Diagnosis;
use Illuminate\Database\Seeder;

class DiagnosisSeeder extends Seeder
{
    public function run(): void
    {
        $diagnoses = [
            // A
            ['name' => 'Achilles Tendinopathy', 'code' => 'AT', 'category_letter' => 'A'],
            ['name' => 'Adhesive Capsulitis', 'code' => 'AC', 'category_letter' => 'A'],
            ['name' => 'Adductor Strain', 'code' => 'AS', 'category_letter' => 'A'],
            ['name' => 'Anterior Cruciate Ligament Injury', 'code' => 'ACL', 'category_letter' => 'A'],
            ['name' => 'Ankle Sprain', 'code' => 'AS', 'category_letter' => 'A'],
            ['name' => 'Ankylosing Spondylitis', 'code' => 'AS', 'category_letter' => 'A'],
            ['name' => 'Anterior Ankle Impingement', 'code' => 'AAI', 'category_letter' => 'A'],
            ['name' => 'Avascular Necrosis', 'code' => 'AVN', 'category_letter' => 'A'],
            ['name' => 'Amyotrophic Lateral Sclerosis', 'code' => 'ALS', 'category_letter' => 'A'],
            ['name' => 'Ataxia', 'code' => 'ATA', 'category_letter' => 'A'],
            ['name' => 'Ataxia-Telangiectasia', 'code' => 'AT', 'category_letter' => 'A'],
            ['name' => 'Autism Spectrum Disorder', 'code' => 'ASD', 'category_letter' => 'A'],

            // B
            ['name' => "Bell's Palsy", 'code' => 'BP', 'category_letter' => 'B'],
            ['name' => 'Biceps Tendinopathy', 'code' => 'BT', 'category_letter' => 'B'],
            ['name' => 'Brachial Plexus Injury', 'code' => 'BPI', 'category_letter' => 'B'],
            ['name' => 'Brain Tumor', 'code' => 'BT', 'category_letter' => 'B'],
            ['name' => 'Brain Injury, Acquired', 'code' => 'ABI', 'category_letter' => 'B'],
            ['name' => 'Brain Injury, Traumatic', 'code' => 'TBI', 'category_letter' => 'B'],
            ['name' => "Broca's Aphasia", 'code' => 'BA', 'category_letter' => 'B'],
            ['name' => 'Bulging Disc', 'code' => 'BD', 'category_letter' => 'B'],

            // C
            ['name' => 'Carpal Tunnel Syndrome', 'code' => 'CTS', 'category_letter' => 'C'],
            ['name' => 'Cervical Disc Herniation', 'code' => 'CDH', 'category_letter' => 'C'],
            ['name' => 'Cervical Radiculopathy', 'code' => 'CR', 'category_letter' => 'C'],
            ['name' => 'Cervical Spondylosis', 'code' => 'CS', 'category_letter' => 'C'],
            ['name' => 'Cervical Spinal Stenosis', 'code' => 'CSS', 'category_letter' => 'C'],
            ['name' => 'Cerebral Palsy', 'code' => 'CP', 'category_letter' => 'C'],
            ['name' => 'Charcot-Marie-Tooth Disease', 'code' => 'CMT', 'category_letter' => 'C'],
            ['name' => 'Chronic Fatigue Syndrome', 'code' => 'CFS', 'category_letter' => 'C'],
            ['name' => 'Chronic Low Back Pain', 'code' => 'CLBP', 'category_letter' => 'C'],
            ['name' => 'Complex Regional Pain Syndrome', 'code' => 'CRPS', 'category_letter' => 'C'],
            ['name' => 'Concussion', 'code' => 'mTBI', 'category_letter' => 'C'],
            ['name' => 'Congenital Muscular Torticollis', 'code' => 'CMT', 'category_letter' => 'C'],
            ['name' => 'Corticobasal Degeneration', 'code' => 'CBD', 'category_letter' => 'C'],
            ['name' => 'Costochondritis', 'code' => 'CC', 'category_letter' => 'C'],
            ['name' => 'Cubital Tunnel Syndrome', 'code' => 'CuTS', 'category_letter' => 'C'],
            ['name' => 'Cystic Fibrosis', 'code' => 'CF', 'category_letter' => 'C'],

            // D
            ['name' => "De Quervain's Tenosynovitis", 'code' => 'DQT', 'category_letter' => 'D'],
            ['name' => 'Degenerative Disc Disease', 'code' => 'DDD', 'category_letter' => 'D'],
            ['name' => 'Developmental Coordination Disorder', 'code' => 'DCD', 'category_letter' => 'D'],
            ['name' => 'Developmental Dysplasia of the Hip', 'code' => 'DDH', 'category_letter' => 'D'],
            ['name' => 'Diabetic Neuropathy', 'code' => 'DN', 'category_letter' => 'D'],
            ['name' => 'Disc Herniation', 'code' => 'DH', 'category_letter' => 'D'],
            ['name' => 'Distal Radius Fracture', 'code' => 'DRF', 'category_letter' => 'D'],
            ['name' => 'Duchenne Muscular Dystrophy', 'code' => 'DMD', 'category_letter' => 'D'],
            ['name' => "Dupuytren's Contracture", 'code' => 'DC', 'category_letter' => 'D'],
            ['name' => 'Deep Vein Thrombosis', 'code' => 'DVT', 'category_letter' => 'D'],

            // E
            ['name' => 'Elbow Osteoarthritis', 'code' => 'EOA', 'category_letter' => 'E'],
            ['name' => 'Encephalitis', 'code' => 'ENC', 'category_letter' => 'E'],
            ['name' => 'Encephalopathy', 'code' => 'EP', 'category_letter' => 'E'],
            ['name' => 'Entrapment Neuropathy', 'code' => 'EN', 'category_letter' => 'E'],
            ['name' => 'Epilepsy', 'code' => 'EPI', 'category_letter' => 'E'],
            ['name' => "Erb's Palsy", 'code' => 'EP', 'category_letter' => 'E'],

            // F
            ['name' => 'Femoroacetabular Impingement', 'code' => 'FAI', 'category_letter' => 'F'],
            ['name' => 'Fibromyalgia', 'code' => 'FM', 'category_letter' => 'F'],
            ['name' => 'Fibular Fracture', 'code' => 'FF', 'category_letter' => 'F'],
            ['name' => 'Frozen Shoulder', 'code' => 'FS', 'category_letter' => 'F'],
            ['name' => 'Facet Joint Syndrome', 'code' => 'FJS', 'category_letter' => 'F'],
            ['name' => 'Foot Drop', 'code' => 'FD', 'category_letter' => 'F'],

            // G
            ['name' => 'Guillain-Barré Syndrome', 'code' => 'GBS', 'category_letter' => 'G'],
            ['name' => 'Glenohumeral Osteoarthritis', 'code' => 'GOA', 'category_letter' => 'G'],
            ['name' => 'Greater Trochanteric Pain Syndrome', 'code' => 'GTPS', 'category_letter' => 'G'],
            ['name' => "Golfer's Elbow", 'code' => 'GE', 'category_letter' => 'G'],
            ['name' => 'Gouty Arthritis', 'code' => 'GA', 'category_letter' => 'G'],

            // H
            ['name' => 'Hamstring Strain', 'code' => 'HS', 'category_letter' => 'H'],
            ['name' => 'Hip Osteoarthritis', 'code' => 'HOA', 'category_letter' => 'H'],
            ['name' => 'Hip Labral Tear', 'code' => 'HLT', 'category_letter' => 'H'],
            ['name' => 'Hip Fracture', 'code' => 'HF', 'category_letter' => 'H'],
            ['name' => 'Herniated Disc', 'code' => 'HD', 'category_letter' => 'H'],
            ['name' => "Huntington's Disease", 'code' => 'HD', 'category_letter' => 'H'],
            ['name' => 'Hemiplegia', 'code' => 'HP', 'category_letter' => 'H'],
            ['name' => 'Hemiparesis', 'code' => 'HMP', 'category_letter' => 'H'],
            ['name' => 'Hydrocephalus', 'code' => 'HC', 'category_letter' => 'H'],
            ['name' => 'Hereditary Spastic Paraplegia', 'code' => 'HSP', 'category_letter' => 'H'],

            // I
            ['name' => 'Iliotibial Band Syndrome', 'code' => 'ITBS', 'category_letter' => 'I'],
            ['name' => 'Impingement Syndrome, Shoulder', 'code' => 'SIS', 'category_letter' => 'I'],
            ['name' => 'Inflammatory Arthritis', 'code' => 'IA', 'category_letter' => 'I'],
            ['name' => 'Intervertebral Disc Disease', 'code' => 'IVDD', 'category_letter' => 'I'],
            ['name' => 'Ischemic Stroke', 'code' => 'IS', 'category_letter' => 'I'],
            ['name' => 'Intracerebral Hemorrhage', 'code' => 'ICH', 'category_letter' => 'I'],
            ['name' => 'Incomplete Spinal Cord Injury', 'code' => 'iSCI', 'category_letter' => 'I'],

            // J
            ['name' => 'Juvenile Idiopathic Arthritis', 'code' => 'JIA', 'category_letter' => 'J'],
            ['name' => 'Juvenile Parkinsonism', 'code' => 'JP', 'category_letter' => 'J'],

            // K
            ['name' => 'Knee Osteoarthritis', 'code' => 'KOA', 'category_letter' => 'K'],
            ['name' => 'Knee Ligament Injury', 'code' => 'KLI', 'category_letter' => 'K'],
            ['name' => 'Knee Meniscal Tear', 'code' => 'KMT', 'category_letter' => 'K'],
            ['name' => "Klumpke's Palsy", 'code' => 'KP', 'category_letter' => 'K'],
            ['name' => 'Kyphosis', 'code' => 'KYP', 'category_letter' => 'K'],

            // L
            ['name' => 'Lateral Epicondylitis', 'code' => 'LE', 'category_letter' => 'L'],
            ['name' => 'Lumbar Disc Herniation', 'code' => 'LDH', 'category_letter' => 'L'],
            ['name' => 'Lumbar Radiculopathy', 'code' => 'LR', 'category_letter' => 'L'],
            ['name' => 'Lumbar Spinal Stenosis', 'code' => 'LSS', 'category_letter' => 'L'],
            ['name' => 'Low Back Pain', 'code' => 'LBP', 'category_letter' => 'L'],
            ['name' => 'Lumbar Spondylosis', 'code' => 'LS', 'category_letter' => 'L'],
            ['name' => 'Lumbosacral Radiculopathy', 'code' => 'LSR', 'category_letter' => 'L'],
            ['name' => 'Lateral Ankle Sprain', 'code' => 'LAS', 'category_letter' => 'L'],
            ['name' => 'Long COVID', 'code' => 'LC', 'category_letter' => 'L'],

            // M
            ['name' => 'Medial Epicondylitis', 'code' => 'ME', 'category_letter' => 'M'],
            ['name' => 'Meniscal Tear', 'code' => 'MT', 'category_letter' => 'M'],
            ['name' => 'Multiple Sclerosis', 'code' => 'MS', 'category_letter' => 'M'],
            ['name' => 'Muscular Dystrophy', 'code' => 'MD', 'category_letter' => 'M'],
            ['name' => 'Myasthenia Gravis', 'code' => 'MG', 'category_letter' => 'M'],
            ['name' => 'Myofascial Pain Syndrome', 'code' => 'MPS', 'category_letter' => 'M'],
            ['name' => 'Myelopathy', 'code' => 'MP', 'category_letter' => 'M'],
            ['name' => 'Myelitis', 'code' => 'MY', 'category_letter' => 'M'],
            ['name' => 'Motor Neuron Disease', 'code' => 'MND', 'category_letter' => 'M'],
            ['name' => 'Multiple System Atrophy', 'code' => 'MSA', 'category_letter' => 'M'],
            ['name' => 'Medial Collateral Ligament Injury', 'code' => 'MCL', 'category_letter' => 'M'],
            ['name' => 'Medial Tibial Stress Syndrome', 'code' => 'MTSS', 'category_letter' => 'M'],
            ['name' => 'Metatarsalgia', 'code' => 'MTA', 'category_letter' => 'M'],
            ['name' => 'Migraine', 'code' => 'MIG', 'category_letter' => 'M'],
            ['name' => 'Musculoskeletal Pain', 'code' => 'MSP', 'category_letter' => 'M'],

            // N
            ['name' => 'Neck Pain', 'code' => 'NP', 'category_letter' => 'N'],
            ['name' => 'Neuropathic Pain', 'code' => 'NP', 'category_letter' => 'N'],
            ['name' => 'Neurogenic Bladder', 'code' => 'NB', 'category_letter' => 'N'],
            ['name' => 'Neurogenic Bowel', 'code' => 'NBO', 'category_letter' => 'N'],
            ['name' => 'Neuromyelitis Optica Spectrum Disorder', 'code' => 'NMOSD', 'category_letter' => 'N'],
            ['name' => 'Neuralgic Amyotrophy', 'code' => 'NA', 'category_letter' => 'N'],
            ['name' => 'Neurogenic Thoracic Outlet Syndrome', 'code' => 'NTOS', 'category_letter' => 'N'],
            ['name' => 'Normal Pressure Hydrocephalus', 'code' => 'NPH', 'category_letter' => 'N'],

            // O
            ['name' => 'Osteoarthritis', 'code' => 'OA', 'category_letter' => 'O'],
            ['name' => 'Osteoporosis', 'code' => 'OP', 'category_letter' => 'O'],
            ['name' => 'Osteomyelitis', 'code' => 'OM', 'category_letter' => 'O'],
            ['name' => 'Osteochondritis Dissecans', 'code' => 'OCD', 'category_letter' => 'O'],
            ['name' => 'Occipital Neuralgia', 'code' => 'ON', 'category_letter' => 'O'],
            ['name' => 'Olecranon Bursitis', 'code' => 'OB', 'category_letter' => 'O'],
            ['name' => 'Orthostatic Tremor', 'code' => 'OT', 'category_letter' => 'O'],

            // P
            ['name' => "Parkinson's Disease", 'code' => 'PD', 'category_letter' => 'P'],
            ['name' => 'Parkinsonism', 'code' => 'PKN', 'category_letter' => 'P'],
            ['name' => 'Patellofemoral Pain Syndrome', 'code' => 'PFPS', 'category_letter' => 'P'],
            ['name' => 'Patellar Tendinopathy', 'code' => 'PT', 'category_letter' => 'P'],
            ['name' => 'Patellar Dislocation', 'code' => 'PD', 'category_letter' => 'P'],
            ['name' => 'Peripheral Neuropathy', 'code' => 'PN', 'category_letter' => 'P'],
            ['name' => 'Peripheral Nerve Injury', 'code' => 'PNI', 'category_letter' => 'P'],
            ['name' => 'Piriformis Syndrome', 'code' => 'PS', 'category_letter' => 'P'],
            ['name' => 'Plantar Fasciitis', 'code' => 'PF', 'category_letter' => 'P'],
            ['name' => 'Polymyalgia Rheumatica', 'code' => 'PMR', 'category_letter' => 'P'],
            ['name' => 'Polymyositis', 'code' => 'PM', 'category_letter' => 'P'],
            ['name' => 'Post-Polio Syndrome', 'code' => 'PPS', 'category_letter' => 'P'],
            ['name' => 'Post-Concussion Syndrome', 'code' => 'PCS', 'category_letter' => 'P'],
            ['name' => 'Post-Stroke Hemiplegia', 'code' => 'PSH', 'category_letter' => 'P'],
            ['name' => 'Posterior Cruciate Ligament Injury', 'code' => 'PCL', 'category_letter' => 'P'],
            ['name' => 'Psoriatic Arthritis', 'code' => 'PsA', 'category_letter' => 'P'],
            ['name' => 'Progressive Supranuclear Palsy', 'code' => 'PSP', 'category_letter' => 'P'],
            ['name' => 'Polyneuropathy', 'code' => 'PNP', 'category_letter' => 'P'],
            ['name' => 'Pseudobulbar Palsy', 'code' => 'PBP', 'category_letter' => 'P'],

            // Q
            ['name' => 'Quadriceps Strain', 'code' => 'QS', 'category_letter' => 'Q'],
            ['name' => 'Quadriplegia', 'code' => 'QP', 'category_letter' => 'Q'],

            // R
            ['name' => 'Radial Tunnel Syndrome', 'code' => 'RTS', 'category_letter' => 'R'],
            ['name' => 'Radiculopathy', 'code' => 'RAD', 'category_letter' => 'R'],
            ['name' => 'Rheumatoid Arthritis', 'code' => 'RA', 'category_letter' => 'R'],
            ['name' => 'Rotator Cuff Tear', 'code' => 'RCT', 'category_letter' => 'R'],
            ['name' => 'Rotator Cuff Tendinopathy', 'code' => 'RCT', 'category_letter' => 'R'],
            ['name' => 'Repetitive Strain Injury', 'code' => 'RSI', 'category_letter' => 'R'],
            ['name' => 'Relapsing-Remitting Multiple Sclerosis', 'code' => 'RRMS', 'category_letter' => 'R'],
            ['name' => 'Residual Limb Pain', 'code' => 'RLP', 'category_letter' => 'R'],
            ['name' => 'Restless Legs Syndrome', 'code' => 'RLS', 'category_letter' => 'R'],

            // S
            ['name' => 'Sacroiliac Joint Dysfunction', 'code' => 'SIJD', 'category_letter' => 'S'],
            ['name' => 'Sciatica', 'code' => 'SCI', 'category_letter' => 'S'],
            ['name' => 'Scoliosis', 'code' => 'SCOL', 'category_letter' => 'S'],
            ['name' => 'Shoulder Osteoarthritis', 'code' => 'SOA', 'category_letter' => 'S'],
            ['name' => 'Shoulder Instability', 'code' => 'SI', 'category_letter' => 'S'],
            ['name' => 'Shoulder Impingement Syndrome', 'code' => 'SIS', 'category_letter' => 'S'],
            ['name' => 'Spinal Cord Injury', 'code' => 'SCI', 'category_letter' => 'S'],
            ['name' => 'Spinal Muscular Atrophy', 'code' => 'SMA', 'category_letter' => 'S'],
            ['name' => 'Spinal Stenosis', 'code' => 'SS', 'category_letter' => 'S'],
            ['name' => 'Spondylolisthesis', 'code' => 'SP', 'category_letter' => 'S'],
            ['name' => 'Spondylolysis', 'code' => 'SL', 'category_letter' => 'S'],
            ['name' => 'Sprain', 'code' => 'SPR', 'category_letter' => 'S'],
            ['name' => 'Strain', 'code' => 'STR', 'category_letter' => 'S'],
            ['name' => 'Stroke', 'code' => 'CVA', 'category_letter' => 'S'],
            ['name' => 'Subacromial Bursitis', 'code' => 'SAB', 'category_letter' => 'S'],
            ['name' => 'Subacromial Pain Syndrome', 'code' => 'SAPS', 'category_letter' => 'S'],
            ['name' => 'Subarachnoid Hemorrhage', 'code' => 'SAH', 'category_letter' => 'S'],
            ['name' => 'Subdural Hematoma', 'code' => 'SDH', 'category_letter' => 'S'],
            ['name' => 'Spasticity', 'code' => 'SP', 'category_letter' => 'S'],
            ['name' => 'Spinocerebellar Ataxia', 'code' => 'SCA', 'category_letter' => 'S'],
            ['name' => 'Spina Bifida', 'code' => 'SB', 'category_letter' => 'S'],
            ['name' => 'Syringomyelia', 'code' => 'SM', 'category_letter' => 'S'],

            // T
            ['name' => 'Tarsal Tunnel Syndrome', 'code' => 'TTS', 'category_letter' => 'T'],
            ['name' => 'Temporomandibular Disorder', 'code' => 'TMD', 'category_letter' => 'T'],
            ['name' => 'Tennis Elbow', 'code' => 'TE', 'category_letter' => 'T'],
            ['name' => 'Thoracic Outlet Syndrome', 'code' => 'TOS', 'category_letter' => 'T'],
            ['name' => 'Thoracic Spine Pain', 'code' => 'TSP', 'category_letter' => 'T'],
            ['name' => 'Tendinopathy', 'code' => 'TP', 'category_letter' => 'T'],
            ['name' => 'Tendon Rupture', 'code' => 'TR', 'category_letter' => 'T'],
            ['name' => 'Traumatic Brain Injury', 'code' => 'TBI', 'category_letter' => 'T'],
            ['name' => 'Transverse Myelitis', 'code' => 'TM', 'category_letter' => 'T'],
            ['name' => 'Trigeminal Neuralgia', 'code' => 'TN', 'category_letter' => 'T'],
            ['name' => 'Torticollis', 'code' => 'TC', 'category_letter' => 'T'],
            ['name' => 'Trigger Finger', 'code' => 'TF', 'category_letter' => 'T'],
            ['name' => 'Trigger Point Myofascial Pain', 'code' => 'TPMP', 'category_letter' => 'T'],

            // U
            ['name' => 'Ulnar Neuropathy', 'code' => 'UN', 'category_letter' => 'U'],
            ['name' => 'Ulnar Collateral Ligament Injury', 'code' => 'UCL', 'category_letter' => 'U'],
            ['name' => 'Upper Motor Neuron Syndrome', 'code' => 'UMNS', 'category_letter' => 'U'],
            ['name' => 'Upper Crossed Syndrome', 'code' => 'UCS', 'category_letter' => 'U'],
            ['name' => 'Unilateral Neglect', 'code' => 'UN', 'category_letter' => 'U'],

            // V
            ['name' => 'Vestibular Neuritis', 'code' => 'VN', 'category_letter' => 'V'],
            ['name' => 'Vestibular Migraine', 'code' => 'VM', 'category_letter' => 'V'],
            ['name' => 'Vertebral Compression Fracture', 'code' => 'VCF', 'category_letter' => 'V'],
            ['name' => 'Vascular Parkinsonism', 'code' => 'VP', 'category_letter' => 'V'],
            ['name' => 'Viral Myelitis', 'code' => 'VM', 'category_letter' => 'V'],

            // W
            ['name' => 'Whiplash-Associated Disorder', 'code' => 'WAD', 'category_letter' => 'W'],
            ['name' => 'Wrist Osteoarthritis', 'code' => 'WOA', 'category_letter' => 'W'],
            ['name' => 'Wrist Sprain', 'code' => 'WS', 'category_letter' => 'W'],
        ];

        foreach ($diagnoses as $data) {
            Diagnosis::firstOrCreate(
                ['name' => $data['name']],
                [
                    'code' => $data['code'],
                    'category_letter' => $data['category_letter'],
                ]
            );
        }
    }
}
