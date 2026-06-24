"use client";

import { useState, useEffect } from "react";
import { Card } from "../../components/ui/card";
import { Badge } from "../../components/ui/badge";
import { Input } from "../../components/ui/input";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "../../components/ui/table";
import { Users, Search, FileText, Music, Video, Phone, Quote, Loader2 } from "lucide-react";

interface Student {
  id: string;
  studentName: string;
  matricNo: string;
  group: string;
  phone: string;
  lifeMotto: string;
}

// Define the API base URL - change 'localhost' to your friend's IP when ready (e.g., 192.168.1.50)
const API_BASE_URL = "http://localhost/mediafind-api";

export default function StudentsPage() {
  const [students, setStudents] = useState<Student[]>([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [isLoading, setIsLoading] = useState(true);

  // 1. Fetch live data from the PHP API when the page loads
  useEffect(() => {
    const fetchStudents = async () => {
      try {
        const res = await fetch(`${API_BASE_URL}/students.php`);
        if (!res.ok) throw new Error("Failed to fetch");
        const data = await res.json();
        setStudents(data);
      } catch (err) {
        console.error("Database connection error:", err);
      } finally {
        setIsLoading(false);
      }
    };
    fetchStudents();
  }, []);

  // 2. Filter the live data based on user input
  const filtered = students.filter(
    (s: Student) =>
      s.studentName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.matricNo.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.group.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.lifeMotto.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // 3. Dynamically calculate group stats based on the fetched data
  const groupStats = [
    { group: "GW01", count: students.filter((s) => s.group === "GW01").length, color: "bg-blue-100 text-blue-700" },
    { group: "GW02", count: students.filter((s) => s.group === "GW02").length, color: "bg-green-100 text-green-700" },
    { group: "GW03", count: students.filter((s) => s.group === "GW03").length, color: "bg-purple-100 text-purple-700" },
    { group: "GW04", count: students.filter((s) => s.group === "GW04").length, color: "bg-orange-100 text-orange-700" },
    { group: "GW05", count: students.filter((s) => s.group === "GW05").length, color: "bg-pink-100 text-pink-700" },
  ];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-slate-900 mb-1">Students</h1>
          <p className="text-slate-600">
            Dataset from bitp3353.utem.edu.my/2026/all — student profiles and multimedia submissions
          </p>
        </div>
        <div className="flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-lg border border-blue-100">
          <Users className="w-5 h-5 text-blue-600" />
          <span className="text-blue-700 font-medium">{students.length} Students Indexed</span>
        </div>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        {groupStats.map((stat) => (
          <Card key={stat.group} className="p-5 rounded-xl border-slate-200 shadow-sm">
            <div className={`inline-flex items-center justify-center w-9 h-9 ${stat.color} rounded-lg mb-3`}>
              <Users className="w-4 h-4" />
            </div>
            <div className="text-2xl font-bold text-slate-900 mb-0.5">{stat.count}</div>
            <div className="text-sm text-slate-600">{stat.group}</div>
          </Card>
        ))}
      </div>

      <Card className="rounded-xl border-slate-200 shadow-sm">
        <div className="p-5 border-b border-slate-200">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <Input
              type="search"
              placeholder="Search by name, matric no, group, or life motto…"
              value={searchTerm}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setSearchTerm(e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {isLoading ? (
          <div className="p-12 text-center flex flex-col items-center">
            <Loader2 className="w-8 h-8 text-blue-600 animate-spin mb-4" />
            <p className="text-slate-600">Connecting to database...</p>
          </div>
        ) : (
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Student Name</TableHead>
                <TableHead>Matric No</TableHead>
                <TableHead>Group</TableHead>
                <TableHead>
                  <div className="flex items-center gap-1"><Phone className="w-3.5 h-3.5" /> Phone</div>
                </TableHead>
                <TableHead>
                  <div className="flex items-center gap-1"><Quote className="w-3.5 h-3.5" /> Life Motto</div>
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filtered.map((student: Student) => (
                <TableRow key={student.id} className="hover:bg-slate-50">
                  <TableCell className="font-medium text-slate-900">{student.studentName}</TableCell>
                  <TableCell className="text-slate-600 font-mono text-sm">{student.matricNo}</TableCell>
                  <TableCell>
                    <Badge variant="outline" className="bg-blue-50 text-blue-700 border-blue-200">
                      {student.group}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-slate-600 text-sm">{student.phone}</TableCell>
                  <TableCell className="text-slate-500 text-sm italic max-w-xs truncate">
                    &quot;{student.lifeMotto}&quot;
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        )}

        {!isLoading && filtered.length === 0 && (
          <div className="p-12 text-center">
            <Users className="w-12 h-12 text-slate-300 mx-auto mb-4" />
            <p className="text-slate-600">No students found matching your search.</p>
          </div>
        )}
      </Card>
    </div>
  );
}