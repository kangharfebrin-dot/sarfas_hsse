const bcrypt = require('bcryptjs');

/**
 * @param { import("knex").Knex } knex
 * @returns { Promise<void> } 
 */
exports.seed = async function(knex) {
  // Disable FK checks and truncate all tables to reset AUTO_INCREMENT to 1
  await knex.raw('SET FOREIGN_KEY_CHECKS = 0');
  const tables = [
    'audit_logs', 'finding_actions', 'findings', 'inspection_answers',
    'inspections', 'inspection_schedules', 'checklist_items', 'checklist_sections',
    'checklist_templates', 'sarfas_assets', 'sarfas_categories', 'locations',
    'users', 'roles'
  ];
  for (const table of tables) {
    await knex.raw('TRUNCATE TABLE `' + table + '`');
  }
  await knex.raw('SET FOREIGN_KEY_CHECKS = 1');

  // 1. Roles
  const [roleSuperAdmin, roleAdminHsse, roleInspektor, roleApprover] = await knex('roles').insert([
    {
      name: 'Super Admin',
      slug: 'super_admin',
      description: 'Akses penuh ke seluruh konfigurasi sistem, user management, dan audit log.'
    },
    {
      name: 'Admin HSSE',
      slug: 'admin_hsse',
      description: 'Pengelola master sarfas, template checklist, jadwal, serta pemantauan temuan.'
    },
    {
      name: 'Inspektor Lapangan',
      slug: 'inspektor',
      description: 'Petugas pelaksana inspeksi sarfas, pengisi checklist lapangan, dan pencatat temuan awal.'
    },
    {
      name: 'Approver / Verifikator',
      slug: 'approver',
      description: 'Pengawas yang berwenang mereview hasil inspeksi dan memverifikasi penutupan temuan.'
    }
  ]);

  // Default password hash for 'password123'
  const hashedPassword = await bcrypt.hash('password123', 10);

  // 2. Users (Demo accounts for Fuel Terminal Maos)
  const [userSuper, userAdmin, userInspektor1, userInspektor2, userApprover] = await knex('users').insert([
    {
      name: 'Budi Santoso (Demo)',
      email: 'superadmin@pertamina.demo',
      password_hash: hashedPassword,
      role_id: 1,
      phone: '081234567890',
      jabatan: 'IT & System Administrator',
      is_active: true
    },
    {
      name: 'Raden Harfebrin (Demo)',
      email: 'admin.hsse@pertamina.demo',
      password_hash: hashedPassword,
      role_id: 2,
      phone: '081298765432',
      jabatan: 'Jr. Officer HSSE FT Maos',
      is_active: true
    },
    {
      name: 'Eko Prasetyo (Demo)',
      email: 'inspektor.maos@pertamina.demo',
      password_hash: hashedPassword,
      role_id: 3,
      phone: '081345678901',
      jabatan: 'Teknisi & Inspektor Sarfas HSSE',
      is_active: true
    },
    {
      name: 'Agus Setiawan (Demo)',
      email: 'inspektor2.maos@pertamina.demo',
      password_hash: hashedPassword,
      role_id: 3,
      phone: '081345678902',
      jabatan: 'Operator & Inspektor Lapangan',
      is_active: true
    },
    {
      name: 'Dwi Cahyono (Demo)',
      email: 'verifikator.hsse@pertamina.demo',
      password_hash: hashedPassword,
      role_id: 4,
      phone: '081122334455',
      jabatan: 'Supervisor HSSE & Operation FT Maos',
      is_active: true
    }
  ]);

  // 3. Locations (FT Maos Operational Units)
  await knex('locations').insert([
    { code: 'LOC-TT01', name: 'Tangki Timbun T-01 s/d T-04 (Pertalite)', description: 'Area tangki timbun bahan bakar minyak produk Pertalite' },
    { code: 'LOC-TT02', name: 'Tangki Timbun T-05 s/d T-08 (Solar/BioSolar)', description: 'Area tangki timbun produk Solar dan BioSolar' },
    { code: 'LOC-FS01', name: 'Filling Shed Mobil Tangki (Bay 1-4)', description: 'Area pengisian mobil tangki distribusi BBM' },
    { code: 'LOC-PH01', name: 'Pump House & Manifold Utama', description: 'Rumah pompa transfer BBM dan manifold interkoneksi pipa' },
    { code: 'LOC-FSP01', name: 'Fire Station & Rumah Pompa Pemadam', description: 'Pos pemadam kebakaran, pompa jockey, diesel fire pump' },
    { code: 'LOC-KP01', name: 'Gedung Kantor Utama & Control Room', description: 'Ruang kerja operasional, monitoring PLC dan administrasi' },
    { code: 'LOC-WS01', name: 'Workshop & Maintenance Yard', description: 'Bengkel mekanikal, elektrikal, dan penyimpanan perkakas' },
    { code: 'LOC-SUB01', name: 'Substation Listrik & Ruang Genset', description: 'Ruang panel distribusi tegangan menengah & darurat genset' },
    { code: 'LOC-TPS01', name: 'TPS Limbah B3 & Oily Water Separator', description: 'Penyimpanan limbah B3 berizin dan separator air berminyak' }
  ]);

  // 4. Sarfas Categories
  await knex('sarfas_categories').insert([
    { code: 'CAT-APAR', name: 'APAR & Proteksi Kebakaran Portabel', description: 'Alat Pemadam Api Ringan (DCP, CO2, Foam, Clean Agent)', icon: 'fire-extinguisher' },
    { code: 'CAT-HYD', name: 'Hydrant, Monitor & Fixed Foam System', description: 'Jaringan pipa air pemadam, hydrant pilar, box hydrant, dan monitor', icon: 'flame' },
    { code: 'CAT-EYE', name: 'Emergency Shower & Eyewash Station', description: 'Peralatan dekontaminasi darurat semprotan badan dan pencuci mata', icon: 'shower-head' },
    { code: 'CAT-SPILL', name: 'Spill Kit & Absorbent System', description: 'Peralatan tanggap tumpahan minyak, absorbent pad, boom, dan serbuk', icon: 'droplet' },
    { code: 'CAT-P3K', name: 'Kotak & Perlengkapan First Aid (P3K)', description: 'Kotak P3K standar Permenakertrans 15/2008 tipe A, B, C', icon: 'cross' },
    { code: 'CAT-RAMBU', name: 'Rambu K3 & Jalur Evakuasi', description: 'Safety signboard, rambu peringatan B3, petunjuk evakuasi, assembly point', icon: 'shield-alert' },
    { code: 'CAT-LISTRIK', name: 'Sistem Grounding & Proteksi Petir', description: 'Pentanahan mobil tangki, grounding tangki, penangkal petir early streamer', icon: 'zap' },
    { code: 'CAT-SCBA', name: 'SCBA & Alat Tanggap Darurat', description: 'Self-Contained Breathing Apparatus, fire jacket, baju tahan panas', icon: 'hard-hat' }
  ]);

  // 5. Sarfas Assets (Demo records for FT Maos)
  await knex('sarfas_assets').insert([
    {
      asset_code: 'APAR-FS-001',
      name: 'APAR Dry Chemical Powder 9 Kg - Bay 1',
      category_id: 1,
      location_id: 3,
      serial_number: 'YM-DCP-2023-8821',
      brand_model: 'Yamato Powder 9 Kg ABC',
      capacity_specs: '9 Kg / 20 Bar Stored Pressure',
      installation_date: '2023-01-15',
      last_inspection_date: '2026-09-20 08:30:00',
      next_inspection_date: '2026-10-20 08:30:00',
      condition_status: 'BAIK',
      photo_url: '/assets/images/demo/apar_fs_01.jpg',
      notes: 'Terpasang pada pilar proteksi Filling Shed Bay 1'
    },
    {
      asset_code: 'APAR-FS-002',
      name: 'APAR Dry Chemical Powder 9 Kg - Bay 3',
      category_id: 1,
      location_id: 3,
      serial_number: 'YM-DCP-2023-8824',
      brand_model: 'Yamato Powder 9 Kg ABC',
      capacity_specs: '9 Kg / 20 Bar Stored Pressure',
      installation_date: '2023-01-15',
      last_inspection_date: '2026-10-02 09:15:00',
      next_inspection_date: '2026-11-02 09:15:00',
      condition_status: 'PERLU_PERHATIAN',
      photo_url: '/assets/images/demo/apar_fs_02.jpg',
      notes: 'Tekanan jarum manometer mendekati batas bawah zona hijau'
    },
    {
      asset_code: 'APAR-KP-005',
      name: 'APAR Clean Agent 6 Kg - Ruang Server Control Room',
      category_id: 1,
      location_id: 6,
      serial_number: 'CH-HCFC-2024-1102',
      brand_model: 'Chubb Halotron Clean Agent 6 Kg',
      capacity_specs: '6 Kg Non-Residue',
      installation_date: '2024-03-10',
      last_inspection_date: '2026-09-28 10:00:00',
      next_inspection_date: '2026-10-28 10:00:00',
      condition_status: 'BAIK',
      photo_url: '/assets/images/demo/apar_server.jpg',
      notes: 'Dikhususkan untuk ruang kontrol dan server elektrikal'
    },
    {
      asset_code: 'APAR-PH-003',
      name: 'APAR CO2 5 Kg - Pump House Manifold',
      category_id: 1,
      location_id: 4,
      serial_number: 'AP-CO2-2022-4419',
      brand_model: 'Appron CO2 5 Kg',
      capacity_specs: '5 Kg High Pressure CO2',
      installation_date: '2022-07-20',
      last_inspection_date: '2026-10-05 11:20:00',
      next_inspection_date: '2026-11-05 11:20:00',
      condition_status: 'RUSAK_RINGAN',
      photo_url: '/assets/images/demo/apar_co2.jpg',
      notes: 'Corong penyemprot (horn) retak pada bagian leher pengunci'
    },
    {
      asset_code: 'HYD-TT-001',
      name: 'Hydrant Pilar & Foam Chamber T-01 (Pertalite)',
      category_id: 2,
      location_id: 1,
      serial_number: 'HYD-M-2021-001',
      brand_model: 'Viking Two-Way 2.5 Inch + Foam Chamber',
      capacity_specs: 'Tekanan Standar 7-10 Bar',
      installation_date: '2021-05-12',
      last_inspection_date: '2026-10-08 08:00:00',
      next_inspection_date: '2026-10-15 08:00:00',
      condition_status: 'BAIK',
      photo_url: '/assets/images/demo/hydrant_tt01.jpg',
      notes: 'Uji flushing rutin tiap minggu, coupling machino terlumasi baik'
    },
    {
      asset_code: 'HYD-FS-002',
      name: 'Hydrant Box & Hose Reel Filling Shed Selatan',
      category_id: 2,
      location_id: 3,
      serial_number: 'HBOX-FS-2022-12',
      brand_model: 'Chubb Box Type B + Kanvas Hose 30m',
      capacity_specs: 'Hose 2.5 inch x 30 meter + Nozzle Jet Spray',
      installation_date: '2022-08-14',
      last_inspection_date: '2026-10-07 14:10:00',
      next_inspection_date: '2026-10-14 14:10:00',
      condition_status: 'BAIK',
      photo_url: '/assets/images/demo/hydrant_box.jpg',
      notes: 'Kunci gembok darurat dan kaca pecah darurat terpasang lengkap'
    },
    {
      asset_code: 'EYE-FS-001',
      name: 'Emergency Shower & Eyewash Filling Shed Bay 1',
      category_id: 3,
      location_id: 3,
      serial_number: 'SPK-SHW-2023-01',
      brand_model: 'Speakman SE-690 Stainless Steel Combination',
      capacity_specs: 'Aliran 75 L/menit shower, 11 L/menit eyewash',
      installation_date: '2023-02-18',
      last_inspection_date: '2026-10-09 09:30:00',
      next_inspection_date: '2026-10-16 09:30:00',
      condition_status: 'BAIK',
      photo_url: '/assets/images/demo/eyewash_fs.jpg',
      notes: 'Debit air jernih, tuas tarik shower dan pijakan kaki eyewash responsif'
    },
    {
      asset_code: 'EYE-TPS-002',
      name: 'Emergency Eyewash Unit TPS Limbah B3',
      category_id: 3,
      location_id: 9,
      serial_number: 'HAWS-EYE-2022-09',
      brand_model: 'Haws 7500 Wall Mounted Gravity Operated',
      capacity_specs: 'Kapasitas Tangki 60 Liter Portable',
      installation_date: '2022-11-05',
      last_inspection_date: '2026-09-25 15:00:00',
      next_inspection_date: '2026-10-02 15:00:00',
      condition_status: 'RUSAK_BERAT',
      photo_url: '/assets/images/demo/eyewash_tps.jpg',
      notes: 'Air dalam tangki keruh berlumut dan tutup nozzle pecah'
    },
    {
      asset_code: 'SPL-FS-001',
      name: 'Mobile Spill Kit 120 Liter - Filling Shed Area',
      category_id: 4,
      location_id: 3,
      serial_number: 'NP-SK-2023-120L',
      brand_model: 'New Pig Wheeled Spill Kit 120L Oil-Only',
      capacity_specs: 'Daya Serap Minyak 120 Liter',
      installation_date: '2023-04-10',
      last_inspection_date: '2026-10-06 10:45:00',
      next_inspection_date: '2026-10-20 10:45:00',
      condition_status: 'BAIK',
      photo_url: '/assets/images/demo/spill_kit.jpg',
      notes: 'Isi lengkap: pad 50 pcs, boom 4 pcs, disposal bag 5 pcs, kacamata & sarung tangan nitrile'
    },
    {
      asset_code: 'P3K-KP-001',
      name: 'Kotak P3K Tipe C Gedung Kantor Lantai 1',
      category_id: 5,
      location_id: 6,
      serial_number: 'P3K-OFF-2024-C1',
      brand_model: 'Onemed Kotak P3K Dinding Kayu Kaca Tipe C',
      capacity_specs: 'Kapasitas 100 Pekerja Standar Kemnaker',
      installation_date: '2024-01-10',
      last_inspection_date: '2026-09-30 11:00:00',
      next_inspection_date: '2026-10-30 11:00:00',
      condition_status: 'PERLU_PERHATIAN',
      photo_url: '/assets/images/demo/p3k_kantor.jpg',
      notes: 'Kasa steril dan perban elastis habis terpakai, belum di-restock'
    },
    {
      asset_code: 'GRD-FS-001',
      name: 'Static Grounding Reel Filling Shed Bay 1-2',
      category_id: 7,
      location_id: 3,
      serial_number: 'NEWT-GR-2022-77',
      brand_model: 'Newson Gale Retractable Grounding Clamp',
      capacity_specs: 'Resistansi < 10 Ohm dengan LED indicator',
      installation_date: '2022-09-01',
      last_inspection_date: '2026-10-10 08:30:00',
      next_inspection_date: '2026-10-24 08:30:00',
      condition_status: 'BAIK',
      photo_url: '/assets/images/demo/grounding.jpg',
      notes: 'Clamp penjepit kabel tembaga kokoh, lampu indikator grounding menyala hijau saat dihubungkan'
    },
    {
      asset_code: 'SCBA-HSSE-001',
      name: 'SCBA Drager PSS 3000 Posko Fire Station',
      category_id: 8,
      location_id: 5,
      serial_number: 'DRG-PSS-2023-14',
      brand_model: 'Drager PSS 3000 Carbon Composite 6.8L',
      capacity_specs: 'Tekanan 300 Bar, Durasi 45 Menit',
      installation_date: '2023-06-15',
      last_inspection_date: '2026-10-04 13:00:00',
      next_inspection_date: '2026-10-18 13:00:00',
      condition_status: 'BAIK',
      photo_url: '/assets/images/demo/scba.jpg',
      notes: 'Tekanan silinder 295 Bar, masker panoramic bersih tanpa goresan'
    }
  ]);

  // 6. Checklist Templates
  const [tmplApar] = await knex('checklist_templates').insert([
    {
      template_code: 'TMPL-APAR-M',
      title: 'Checklist Inspeksi Rutin Bulanan APAR (NFPA 10 / HSSE Pertamina)',
      category_id: 1,
      inspection_frequency: 'BULANAN',
      description: 'Pemeriksaan kelayakan operasional APAR portabel di seluruh unit kerja FT Maos.',
      version: '2.1',
      is_active: true
    }
  ]);

  const [tmplHydrant] = await knex('checklist_templates').insert([
    {
      template_code: 'TMPL-HYD-W',
      title: 'Checklist Inspeksi Mingguan Hydrant Pilar & Hose Reel',
      category_id: 2,
      inspection_frequency: 'MINGGUAN',
      description: 'Pemeriksaan kesiapan instalasi air pemadam, pilar hydrant, coupling, dan box selang.',
      version: '1.4',
      is_active: true
    }
  ]);

  const [tmplEyewash] = await knex('checklist_templates').insert([
    {
      template_code: 'TMPL-EYE-W',
      title: 'Checklist Inspeksi Mingguan Emergency Shower & Eyewash',
      category_id: 3,
      inspection_frequency: 'MINGGUAN',
      description: 'Pemeriksaan fungsi pancuran air darurat kimia, kebersihan tangki dan drainase.',
      version: '1.2',
      is_active: true
    }
  ]);

  // 7. Sections & Items for APAR
  const [secAparFisik] = await knex('checklist_sections').insert([
    { template_id: 1, title: 'Kondisi Fisik Tabung & Kelengkapan', sort_order: 1, description: 'Pemeriksaan fisik luar tabung dan tanda pengenal' }
  ]);
  const [secAparMekanis] = await knex('checklist_sections').insert([
    { template_id: 1, title: 'Indikator Tekanan & Mekanisme Pengeluaran', sort_order: 2, description: 'Pemeriksaan jarum tekanan, selang, nozzle, dan segel' }
  ]);
  const [secAparAkses] = await knex('checklist_sections').insert([
    { template_id: 1, title: 'Aksesibilitas & Penempatan', sort_order: 3, description: 'Pemeriksaan rambu dan kemudahan akses di lapangan' }
  ]);

  await knex('checklist_items').insert([
    {
      section_id: secAparFisik,
      item_code: 'APAR-01',
      question_text: 'Tabung APAR dalam kondisi bersih, bebas karat/korosi, penyok, atau kerusakan struktural.',
      standard_criteria: 'Tidak ada korosi parah, cat merah tidak mengelupas drastis, tidak ada bekas benturan keras.',
      is_critical: false,
      requires_photo: false,
      sort_order: 1
    },
    {
      section_id: secAparFisik,
      item_code: 'APAR-02',
      question_text: 'Label petunjuk pemakaian (PASS) dan kartu inspeksi (tag) terpasang jelas dan terbaca.',
      standard_criteria: 'Tulisan PASS terbaca, kartu inspeksi terisi tanggal bulan berjalan.',
      is_critical: false,
      requires_photo: false,
      sort_order: 2
    },
    {
      section_id: secAparMekanis,
      item_code: 'APAR-03',
      question_text: 'Jarum penunjuk manometer berada tepat pada zona hijau (tekanan kerja 12 - 15 Bar).',
      standard_criteria: 'Indikator tekanan berada di rentang hijau. Jarum tidak drop di zona merah kiri atau over di kanan.',
      is_critical: true,
      requires_photo: true,
      sort_order: 3
    },
    {
      section_id: secAparMekanis,
      item_code: 'APAR-04',
      question_text: 'Pin pengaman (safety pin) dan segel plastik utuh belum terputus atau terlepas.',
      standard_criteria: 'Segel pengunci masih terpasang rapi, membuktikan APAR belum pernah dipakai tanpa otorisasi.',
      is_critical: true,
      requires_photo: false,
      sort_order: 4
    },
    {
      section_id: secAparMekanis,
      item_code: 'APAR-05',
      question_text: 'Selang (hose) fleksibel tidak retak/getas, dan corong/nozzle bebas dari sumbatan serangga atau kotoran.',
      standard_criteria: 'Karet selang elastis, tidak ada retakan, lubang keluar bersih dan terlindung.',
      is_critical: false,
      requires_photo: false,
      sort_order: 5
    },
    {
      section_id: secAparAkses,
      item_code: 'APAR-06',
      question_text: 'Akses menuju APAR bebas dari halangan barang/perkakas (jarak bebas radius minimum 1 meter).',
      standard_criteria: 'Tidak tertutup drum, palet, atau kendaraan. Mudah dijangkau seketika dalam keadaan darurat.',
      is_critical: true,
      requires_photo: true,
      sort_order: 6
    },
    {
      section_id: secAparAkses,
      item_code: 'APAR-07',
      question_text: 'Ketinggian penempatan tabung sesuai standar (maksimal 1.2 meter dari lantai) dan rambu APAR terlihat.',
      standard_criteria: 'Tergantung pada braket kokoh atau dalam box proteksi cuaca.',
      is_critical: false,
      requires_photo: false,
      sort_order: 7
    }
  ]);

  // Items for Hydrant
  const [secHydPilar] = await knex('checklist_sections').insert([
    { template_id: 2, title: 'Kondisi Fisik Pilar & Valve', sort_order: 1, description: 'Pemeriksaan fisik pilar hydrant dan katup' }
  ]);
  await knex('checklist_items').insert([
    {
      section_id: secHydPilar,
      item_code: 'HYD-01',
      question_text: 'Pilar hydrant tidak bocor pada valve maupun flange sambungan pipa bawah tanah.',
      standard_criteria: 'Kering, tidak ada rembesan air bertekanan.',
      is_critical: true,
      requires_photo: true,
      sort_order: 1
    },
    {
      section_id: secHydPilar,
      item_code: 'HYD-02',
      question_text: 'Kopling machino/storz dilengkapi penutup rantai (blank cap) dan ulir terlumasi grease.',
      standard_criteria: 'Cap terpasang, tidak berkarat macet saat dibuka.',
      is_critical: false,
      requires_photo: false,
      sort_order: 2
    },
    {
      section_id: secHydPilar,
      item_code: 'HYD-03',
      question_text: 'Kunci pembuka spindle (hydrant wrench) tersedia dan pas pada kepala valve.',
      standard_criteria: 'Tersedia di dekat lokasi atau dalam box hydrant.',
      is_critical: true,
      requires_photo: false,
      sort_order: 3
    }
  ]);

  // 8. Inspection Schedules
  await knex('inspection_schedules').insert([
    {
      title: 'Inspeksi Rutin Bulanan APAR Filling Shed & Area Pompa',
      sarfas_id: 1,
      template_id: 1,
      assigned_user_id: 3, // Eko
      scheduled_date: '2026-10-20',
      frequency: 'BULANAN',
      status: 'TERJADWAL',
      notes: 'Pemeriksaan periodik seluruh tabung DCP di Filling Shed Bay 1-4'
    },
    {
      title: 'Uji Kesiapan Hydrant & Foam Chamber Tangki Timbun',
      sarfas_id: 5,
      template_id: 2,
      assigned_user_id: 3, // Eko
      scheduled_date: '2026-10-15',
      frequency: 'MINGGUAN',
      status: 'TERJADWAL',
      notes: 'Flushing jalur pipa pemadam dan uji valve utama'
    },
    {
      title: 'Inspeksi Darurat Eyewash Unit TPS Limbah B3 (Tindak Lanjut)',
      sarfas_id: 8,
      template_id: 3,
      assigned_user_id: 4, // Agus
      scheduled_date: '2026-10-11',
      frequency: 'MINGGUAN',
      status: 'TERLAMBAT',
      notes: 'Pemeriksaan ulang setelah pergantian air reservoar eyewash'
    },
    {
      title: 'Inspeksi Rutin Spill Kit Filling Shed',
      sarfas_id: 9,
      template_id: 1,
      assigned_user_id: 3,
      scheduled_date: '2026-10-22',
      frequency: 'BULANAN',
      status: 'TERJADWAL',
      notes: 'Cek stok absorbent pad dan containment boom'
    }
  ]);

  // 9. Sample Inspections
  const [insp1] = await knex('inspections').insert([
    {
      inspection_no: 'INSP-202610-001',
      sarfas_id: 1, // APAR-FS-001
      template_id: 1,
      inspector_id: 3,
      inspection_date: '2026-10-10',
      status: 'SELESAI',
      summary_condition: 'BAIK',
      general_notes: 'Pemeriksaan APAR Bay 1 berlangsung normal. Tekanan ideal 14 Bar, segel utuh, akses bersih.',
      inspector_signature: 'DigitalSignature_Eko_Prasetyo_20261010',
      verified_by: 5,
      verified_at: '2026-10-10 16:30:00',
      verification_notes: 'Hasil inspeksi telah diverifikasi oleh Spv HSSE. Tabung siap operasi.'
    }
  ]);

  const [insp2] = await knex('inspections').insert([
    {
      inspection_no: 'INSP-202610-002',
      sarfas_id: 4, // APAR-PH-003
      template_id: 1,
      inspector_id: 3,
      inspection_date: '2026-10-11',
      status: 'MENUNGGU_REVIEW',
      summary_condition: 'PERLU_TINDAK_LANJUT',
      general_notes: 'Ditemukan corong nozzle CO2 retak pada klem sambungan. Tabung masih bertekanan baik.',
      inspector_signature: 'DigitalSignature_Eko_Prasetyo_20261011',
      verified_by: null,
      verified_at: null,
      verification_notes: null
    }
  ]);

  const [insp3] = await knex('inspections').insert([
    {
      inspection_no: 'INSP-202610-003',
      sarfas_id: 8, // EYE-TPS-002
      template_id: 3,
      inspector_id: 4,
      inspection_date: '2026-10-12',
      status: 'MENUNGGU_REVIEW',
      summary_condition: 'KRITIS',
      general_notes: 'Kondisi air eyewash kotor dan tutup pelindung debu hilang. Sangat berbahaya jika terkena paparan kimia langsung.',
      inspector_signature: 'DigitalSignature_Agus_Setiawan_20261012',
      verified_by: null,
      verified_at: null,
      verification_notes: null
    }
  ]);

  // 10. Inspection Answers for INSP-202610-001 (All BAIK)
  await knex('inspection_answers').insert([
    { inspection_id: insp1, checklist_item_id: 1, answer: 'BAIK', notes: 'Tabung bersih mulus' },
    { inspection_id: insp1, checklist_item_id: 2, answer: 'BAIK', notes: 'Tag inspeksi terpasang rapi' },
    { inspection_id: insp1, checklist_item_id: 3, answer: 'BAIK', notes: 'Jarum stabil di angka 14 Bar zona hijau', photo_url: '/assets/images/demo/manometer_green.jpg' },
    { inspection_id: insp1, checklist_item_id: 4, answer: 'BAIK', notes: 'Segel oranye utuh' },
    { inspection_id: insp1, checklist_item_id: 5, answer: 'BAIK', notes: 'Selang elastis, nozzle bersih' },
    { inspection_id: insp1, checklist_item_id: 6, answer: 'BAIK', notes: 'Akses bebas halangan', photo_url: '/assets/images/demo/access_clear.jpg' },
    { inspection_id: insp1, checklist_item_id: 7, answer: 'BAIK', notes: 'Tinggi gantung 1.1 meter' }
  ]);

  // Inspection Answers for INSP-202610-002 (Item 5 Rusak Ringan)
  await knex('inspection_answers').insert([
    { inspection_id: insp2, checklist_item_id: 1, answer: 'BAIK', notes: 'Kondisi tabung CO2 baik' },
    { inspection_id: insp2, checklist_item_id: 2, answer: 'BAIK', notes: 'Label PASS jelas' },
    { inspection_id: insp2, checklist_item_id: 3, answer: 'BAIK', notes: 'Bobot tabung sesuai standar tara' },
    { inspection_id: insp2, checklist_item_id: 4, answer: 'BAIK', notes: 'Pin terpasang' },
    { inspection_id: insp2, checklist_item_id: 5, answer: 'TIDAK_BAIK', notes: 'Corong penyemprot retak 3 cm pada pangkal ulir', photo_url: '/assets/images/demo/retak_nozzle.jpg', risk_level: 'SEDANG' },
    { inspection_id: insp2, checklist_item_id: 6, answer: 'BAIK', notes: 'Lokasi pump house mudah dijangkau' },
    { inspection_id: insp2, checklist_item_id: 7, answer: 'BAIK', notes: 'Tergantung pada dinding' }
  ]);

  // 11. Findings & Corrective Actions
  const [f1] = await knex('findings').insert([
    {
      finding_no: 'FIND-202610-001',
      inspection_id: insp2,
      sarfas_id: 4, // APAR-PH-003
      checklist_item_id: 5,
      title: 'Corong (Discharge Horn) APAR CO2 Retak di Rumah Pompa',
      description: 'Pada saat inspeksi rutin bulanan, ditemukan corong plastik hitam APAR CO2 retak memanjang. Risiko patah saat ditekan dan semprotan gas CO2 mengenai tangan operator (cold burn hazard).',
      risk_level: 'SEDANG',
      status: 'DALAM_TINDAK_LANJUT',
      reported_by: 3, // Eko
      assigned_to: 2, // Harfebrin (Admin HSSE)
      due_date: '2026-10-18',
      photo_before: '/assets/images/demo/horn_cracked.jpg',
      corrective_action_plan: 'Mengajukan penggantian corong dan selang spare part baru dari gudang logistik HSSE.',
      corrective_action_notes: 'Spare part nozzle CO2 tipe Appron telah diminta dari gudang pusat per 12 Oktober.'
    }
  ]);

  const [f2] = await knex('findings').insert([
    {
      finding_no: 'FIND-202610-002',
      inspection_id: insp3,
      sarfas_id: 8, // EYE-TPS-002
      checklist_item_id: 8,
      title: 'Reservoar Air Emergency Eyewash Berlumut & Tutup Debu Pecah',
      description: 'Air pada penampung eyewash portable di TPS Limbah B3 keruh, berbau, dan terdapat endapan lumut. Tutup mangkuk pelindung debu pecah sehingga serangga masuk.',
      risk_level: 'KRITIS',
      status: 'BARU',
      reported_by: 4, // Agus
      assigned_to: 2, // Admin HSSE
      due_date: '2026-10-14',
      photo_before: '/assets/images/demo/eyewash_dirty.jpg',
      corrective_action_plan: 'Kuras total reservoar, lakukan disinfeksi dengan tablet anti-bakteri, pasang tutup pelindung baru, dan isi ulang air steril.',
      corrective_action_notes: null
    }
  ]);

  const [f3] = await knex('findings').insert([
    {
      finding_no: 'FIND-202609-089',
      inspection_id: null,
      sarfas_id: 2, // APAR-FS-002
      checklist_item_id: 3,
      title: 'Manometer APAR Dry Powder Mendekati Garis Merah',
      description: 'Tekanan nitrogen pendorong pada tabung 9 kg di Filling Shed Bay 3 turun menjadi 10.5 Bar (batas bawah hijau).',
      risk_level: 'RENDAH',
      status: 'SELESAI',
      reported_by: 3,
      assigned_to: 3,
      due_date: '2026-10-05',
      photo_before: '/assets/images/demo/pressure_low.jpg',
      photo_after: '/assets/images/demo/pressure_restored.jpg',
      corrective_action_plan: 'Lakukan pengisian ulang tekanan nitrogen (recharge N2) di workshop HSSE.',
      corrective_action_notes: 'Tabung telah dibawa ke bengkel dan diisi ulang gas N2 hingga 14.5 Bar.',
      verified_by: 5, // Dwi (Spv HSSE)
      verified_at: '2026-10-05 15:45:00',
      verification_notes: 'Verifikasi fisik selesai. Tekanan jarum tegak lurus zona hijau dan segel baru telah dipasang.'
    }
  ]);

  // 12. Finding Action Log
  await knex('finding_actions').insert([
    {
      finding_id: f1,
      user_id: 3,
      action_type: 'CREATED',
      notes: 'Temuan dicatat dari hasil inspeksi checklist INSP-202610-002',
      photo_proof: '/assets/images/demo/horn_cracked.jpg',
      created_at: '2026-10-11 11:30:00'
    },
    {
      finding_id: f1,
      user_id: 2,
      action_type: 'IN_PROGRESS',
      notes: 'Permintaan suku cadang horn CO2 telah dikirimkan ke bagian pengadaan logistik.',
      photo_proof: null,
      created_at: '2026-10-12 09:15:00'
    },
    {
      finding_id: f2,
      user_id: 4,
      action_type: 'CREATED',
      notes: 'Temuan kritis dicatat segera setelah pemeriksaan lapangan di TPS LB3.',
      photo_proof: '/assets/images/demo/eyewash_dirty.jpg',
      created_at: '2026-10-12 14:00:00'
    },
    {
      finding_id: f3,
      user_id: 3,
      action_type: 'SUBMITTED_RESOLUTION',
      notes: 'Pengisian nitrogen selesai dikerjakan teknisi workshop.',
      photo_proof: '/assets/images/demo/pressure_restored.jpg',
      created_at: '2026-10-05 14:00:00'
    },
    {
      finding_id: f3,
      user_id: 5,
      action_type: 'VERIFIED_CLOSED',
      notes: 'Pemeriksaan lapangan oleh Spv HSSE menyatakan tabung normal dan temuan ditutup.',
      photo_proof: '/assets/images/demo/pressure_restored.jpg',
      created_at: '2026-10-05 15:45:00'
    }
  ]);

  // 13. Audit Logs
  await knex('audit_logs').insert([
    {
      user_id: 3,
      user_name: 'Eko Prasetyo',
      action: 'SUBMIT_INSPECTION',
      module: 'INSPEKSI',
      record_id: 'INSP-202610-001',
      details: 'Menyelesaikan pengisian formulir inspeksi APAR-FS-001 dengan hasil BAIK.',
      ip_address: '192.168.1.45',
      created_at: '2026-10-10 10:15:00'
    },
    {
      user_id: 5,
      user_name: 'Dwi Cahyono',
      action: 'APPROVE_INSPECTION',
      module: 'INSPEKSI',
      record_id: 'INSP-202610-001',
      details: 'Menyetujui dan memverifikasi laporan inspeksi APAR-FS-001.',
      ip_address: '192.168.1.10',
      created_at: '2026-10-10 16:30:00'
    },
    {
      user_id: 3,
      user_name: 'Eko Prasetyo',
      action: 'CREATE_FINDING',
      module: 'TEMUAN',
      record_id: 'FIND-202610-001',
      details: 'Membuat temuan risiko SEDANG untuk sarfas APAR-PH-003.',
      ip_address: '192.168.1.45',
      created_at: '2026-10-11 11:30:00'
    },
    {
      user_id: 4,
      user_name: 'Agus Setiawan',
      action: 'CREATE_FINDING',
      module: 'TEMUAN',
      record_id: 'FIND-202610-002',
      details: 'Mencatat temuan kondisi KRITIS pada Emergency Eyewash TPS-002.',
      ip_address: '192.168.1.48',
      created_at: '2026-10-12 14:00:00'
    }
  ]);
};
