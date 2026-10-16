<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCluster;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Create 4 Service Clusters
        $cluster1 = ServiceCluster::updateOrCreate(['id' => 1], [
            'cluster_name' => 'Mechanical & Automation Engineering',
            'cluster_tag' => 'CLUSTER 01',
            'cluster_icon' => 'precision_manufacturing',
            'sort_order' => 1,
        ]);

        $cluster2 = ServiceCluster::updateOrCreate(['id' => 2], [
            'cluster_name' => 'Electrical Systems, PLC & Maintenance',
            'cluster_tag' => 'CLUSTER 02',
            'cluster_icon' => 'bolt',
            'sort_order' => 2,
        ]);

        $cluster3 = ServiceCluster::updateOrCreate(['id' => 3], [
            'cluster_name' => 'Supply & Industrial Procurement',
            'cluster_tag' => 'CLUSTER 03',
            'cluster_icon' => 'inventory_2',
            'sort_order' => 3,
        ]);

        $cluster4 = ServiceCluster::updateOrCreate(['id' => 4], [
            'cluster_name' => 'Special Construction & Workforce Solutions',
            'cluster_tag' => 'CLUSTER 04',
            'cluster_icon' => 'engineering',
            'sort_order' => 4,
        ]);

        // Create 12 Services
        Service::updateOrCreate(['slug' => 'mechanical-electrical'], [
            'service_cluster_id' => $cluster1->id,
            'short_title' => 'Mechanical & Electrical',
            'service_title' => 'Mechanical and Electrical',
            'service_description' => 'Rekayasa mekanikal presisi dan instalasi kelistrikan industri terpadu.',
            'service_icon' => 'precision_manufacturing',
            'sort_order' => 1,
        ]);

        Service::updateOrCreate(['slug' => 'digital-automation'], [
            'service_cluster_id' => $cluster1->id,
            'short_title' => 'Digital Automation & Controlling',
            'service_title' => 'Digital Automation and Controlling',
            'service_description' => 'Sistem kendali terotomasi modern dengan PLC/SCADA.',
            'service_icon' => 'smart_toy',
            'sort_order' => 2,
        ]);

        Service::updateOrCreate(['slug' => 'jigs-fixtures'], [
            'service_cluster_id' => $cluster1->id,
            'short_title' => 'Jigs & Fixtures',
            'service_title' => 'Jigs and Fixtures',
            'service_description' => 'Fabrikasi jig presisi dan clamping fixture.',
            'service_icon' => 'architecture',
            'sort_order' => 3,
        ]);

        Service::updateOrCreate(['slug' => 'unload-robot-chucking'], [
            'service_cluster_id' => $cluster1->id,
            'short_title' => 'Unload Robot Chucking',
            'service_title' => 'Unload Robot Chucking',
            'service_description' => 'Sistem chucking otomatis untuk robot industri.',
            'service_icon' => 'hardware',
            'sort_order' => 4,
        ]);

        Service::updateOrCreate(['slug' => 'material-handling'], [
            'service_cluster_id' => $cluster1->id,
            'short_title' => 'Material Handling',
            'service_title' => 'Material Handling',
            'service_description' => 'Konveyor dan sistem logistik internal pabrik.',
            'service_icon' => 'forklift',
            'sort_order' => 5,
        ]);

        Service::updateOrCreate(['slug' => 'distribution-panel'], [
            'service_cluster_id' => $cluster2->id,
            'short_title' => 'Distribution Panel, PLC, HMI',
            'service_title' => 'Distribution Panel Assembly, Capacitor Bank, PLC, HMI',
            'service_description' => 'Perakitan panel distribusi dan pemrograman HMI.',
            'service_icon' => 'developer_board',
            'sort_order' => 6,
        ]);

        Service::updateOrCreate(['slug' => 'electrical-installation'], [
            'service_cluster_id' => $cluster2->id,
            'short_title' => 'Electrical & Machine Maintenance',
            'service_title' => 'Electrical Installation and Machine Maintenance',
            'service_description' => 'Perawatan preventif peralatan listrik dan mesin.',
            'service_icon' => 'electrical_services',
            'sort_order' => 7,
        ]);

        Service::updateOrCreate(['slug' => 'electrical-equipment-supplier'], [
            'service_cluster_id' => $cluster3->id,
            'short_title' => 'Electrical Equipment Supplier',
            'service_title' => 'Industrial Electrical Equipment Supplier',
            'service_description' => 'Pengadaan komponen kelistrikan industri.',
            'service_icon' => 'inventory_2',
            'sort_order' => 8,
        ]);

        Service::updateOrCreate(['slug' => 'grease-system'], [
            'service_cluster_id' => $cluster2->id,
            'short_title' => 'Auto Grease System (Loader)',
            'service_title' => 'Automatically Grease System For Loader Part',
            'service_description' => 'Sistem lubrikasi otomatis untuk alat berat.',
            'service_icon' => 'oil_barrel',
            'sort_order' => 9,
        ]);

        Service::updateOrCreate(['slug' => 'general-supplier'], [
            'service_cluster_id' => $cluster3->id,
            'short_title' => 'Industrial Machinery & Parts',
            'service_title' => 'General Supplier Of Industrial Machinery, Spare Parts, Accessories, Equipment, Construction Material, And Others Goods',
            'service_description' => 'Pemasok peralatan industri dan suku cadang.',
            'service_icon' => 'conveyor_belt',
            'sort_order' => 10,
        ]);

        Service::updateOrCreate(['slug' => 'special-construction'], [
            'service_cluster_id' => $cluster4->id,
            'short_title' => 'Other Special Construction',
            'service_title' => 'Other Special Construction (YTDL)',
            'service_description' => 'Pekerjaan konstruksi spesialis industri.',
            'service_icon' => 'construction',
            'sort_order' => 11,
        ]);

        Service::updateOrCreate(['slug' => 'labor-provision'], [
            'service_cluster_id' => $cluster4->id,
            'short_title' => 'Workforce & Labor Solutions',
            'service_title' => 'Selection, Placement And Provision Activities domestic Part-time Labor',
            'service_description' => 'Penyediaan tenaga kerja terampil manufaktur.',
            'service_icon' => 'badge',
            'sort_order' => 12,
        ]);
    }
}
