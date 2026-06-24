"use client";

import { useState, useEffect } from "react";
import { Card } from "../../components/ui/card";
import { Input } from "../../components/ui/input";
import { Button } from "../../components/ui/button";
import { Badge } from "../../components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../../components/ui/select";
import { Search, X, FileText, Music, Video, Phone, Quote, Calendar, Loader2 } from "lucide-react";

export type SearchType = "ABR" | "TBR" | "CBR";
export type FileType = "All" | "PDF" | "MP3" | "MP4";
export type StudentGroup = "All" | "GW01" | "GW02" | "GW03" | "GW04" | "GW05";
export type FileSize = "All" | "Small" | "Medium" | "Large";
export type VideoResolution = "All" | "720p" | "1080p";
export type MoodLabel = "All" | "energetic" | "calm";

export interface SearchFilters {
  keyword: string;
  fileType: FileType;
  studentGroup: StudentGroup;
  dateFrom: string;
  dateTo: string;
  fileSize: FileSize;
  audioMinDuration: string;
  audioMaxDuration: string;
  moodLabel: MoodLabel;
  videoResolution: VideoResolution;
}

// THIS FIXES THE "UNEXPECTED ANY" ERROR
export interface SearchResult {
  id?: string | number;
  fileName: string;
  fileType: string;
  studentName: string;
  matricNo: string;
  group: string;
  phone?: string;
  uploadDate?: string;
  fileSizeMb?: number;
  lifeMotto?: string;
}

const RETRIEVAL_MODES: { mode: SearchType; label: string; description: string; color: string }[] = [
  { mode: "ABR", label: "Attribute-Based Retrieval", description: "Filter by file properties: type, size, date, group", color: "purple" },
  { mode: "TBR", label: "Text-Based Retrieval", description: "Search keywords in names, mottos, and PDF content", color: "green" },
  { mode: "CBR", label: "Content-Based Retrieval", description: "Find files by audio duration, mood, or video resolution", color: "orange" },
];

const API_BASE_URL = "http://localhost/mediafind-api";

export default function SearchFilesPage() {
  const [activeMode, setActiveMode] = useState<SearchType>("TBR");
  const [filters, setFilters] = useState<SearchFilters>({
    keyword: "", fileType: "All", studentGroup: "All", dateFrom: "", dateTo: "",
    fileSize: "All", audioMinDuration: "", audioMaxDuration: "", moodLabel: "All", videoResolution: "All",
  });
  
  // Replaced <any[]> with <SearchResult[]>
  const [searchResults, setSearchResults] = useState<SearchResult[]>([]);
  const [dbStats, setDbStats] = useState({ totalFiles: 0, pdfCount: 0, audioCount: 0, videoCount: 0 });
  
  const [hasSearched, setHasSearched] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    fetch(`${API_BASE_URL}/dashboard-stats.php`)
      .then(res => res.json())
      .then(data => setDbStats(data))
      .catch(err => console.error("Failed to load dashboard stats", err));
  }, []);

  const handleSearch = async () => {
    setHasSearched(true);
    setIsLoading(true);

    try {
      const queryParams = new URLSearchParams({
        mode: activeMode,
        keyword: filters.keyword,
        fileType: filters.fileType,
        group: filters.studentGroup,
        size: filters.fileSize,
        resolution: filters.videoResolution,
        mood: filters.moodLabel,
        minDuration: filters.audioMinDuration,
        maxDuration: filters.audioMaxDuration
      });

      const res = await fetch(`${API_BASE_URL}/search.php?${queryParams}`);
      if (!res.ok) throw new Error("Search query failed");
      
      const data = await res.json();
      setSearchResults(data);

    } catch (err) {
      console.error("Failed to fetch search results from DB", err);
      setSearchResults([]); 
    } finally {
      setIsLoading(false);
    }
  };

  const handleClear = () => {
    setFilters({
      keyword: "", fileType: "All", studentGroup: "All", dateFrom: "", dateTo: "",
      fileSize: "All", audioMinDuration: "", audioMaxDuration: "", moodLabel: "All", videoResolution: "All",
    });
    setSearchResults([]); 
    setHasSearched(false);
  };

  const getFileIcon = (fileType: string) => {
    switch (fileType) {
      case "PDF": return <FileText className="w-10 h-10 text-red-500" />;
      case "MP3": return <Music className="w-10 h-10 text-orange-500" />;
      case "MP4": return <Video className="w-10 h-10 text-blue-500" />;
      default: return <FileText className="w-10 h-10 text-slate-500" />;
    }
  };

  const getFileTypeBadgeColor = (fileType: string) => {
    switch (fileType) {
      case "PDF": return "bg-red-100 text-red-700 border-red-200";
      case "MP3": return "bg-orange-100 text-orange-700 border-orange-200";
      case "MP4": return "bg-blue-100 text-blue-700 border-blue-200";
      default: return "bg-slate-100 text-slate-700 border-slate-200";
    }
  };

  const modeColors: Record<SearchType, string> = { ABR: "purple", TBR: "green", CBR: "orange" };
  const activeColor = modeColors[activeMode];

  return (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-8 text-white">
        <h1 className="text-3xl font-bold mb-1">Search Multimedia Submissions</h1>
        <p className="text-blue-100">
          BITP3353 — Multimedia Database Systems &nbsp;|&nbsp; Dataset: bitp3353.utem.edu.my/2026/all
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {RETRIEVAL_MODES.map(({ mode, label, description, color }) => {
          const isActive = activeMode === mode;
          return (
            <button
              key={mode}
              onClick={() => setActiveMode(mode)}
              className={`text-left p-4 rounded-xl border-2 transition-all ${
                isActive ? `border-${color}-500 bg-${color}-50` : "border-slate-200 bg-white hover:border-slate-300"
              }`}
            >
              <div className="flex items-center gap-2 mb-1">
                <Badge className={isActive ? `bg-${color}-600 text-white border-${color}-600` : "bg-slate-200 text-slate-700 border-slate-200"}>
                  {mode}
                </Badge>
                <span className={`font-semibold text-sm ${isActive ? `text-${color}-800` : "text-slate-700"}`}>
                  {label}
                </span>
              </div>
              <p className={`text-xs ${isActive ? `text-${color}-600` : "text-slate-500"}`}>{description}</p>
            </button>
          );
        })}
      </div>

      <Card className="p-5 rounded-xl border-slate-200 shadow-sm">
        <div className="flex gap-3">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <Input
              type="text"
              placeholder={
                activeMode === "TBR" ? "Enter keyword (e.g. Johor, human, technology)…"
                : activeMode === "ABR" ? "Enter file name, matric no, or group…"
                : "Enter mood label or content feature…"
              }
              value={filters.keyword}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setFilters({ ...filters, keyword: e.target.value })}
              onKeyDown={(e: React.KeyboardEvent<HTMLInputElement>) => e.key === "Enter" && handleSearch()}
              className="pl-10 h-11"
            />
          </div>
          <Button onClick={handleSearch} disabled={isLoading} className="h-11 px-7 bg-blue-600 hover:bg-blue-700">
            {isLoading ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Search className="w-4 h-4 mr-2" />}
            {isLoading ? "Searching..." : "Search"}
          </Button>
          <Button onClick={handleClear} variant="outline" className="h-11 px-5">
            <X className="w-4 h-4 mr-2" />
            Clear
          </Button>
        </div>
      </Card>

      <div className="grid lg:grid-cols-3 gap-6 mt-6">
        <div className="lg:col-span-2 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-semibold text-slate-900">
              {hasSearched ? `Results (${searchResults.length})` : "Awaiting Search..."}
            </h2>
            {hasSearched && (
              <Badge className={`bg-${activeColor}-100 text-${activeColor}-700 border-${activeColor}-200 border`}>
                {activeMode} — {RETRIEVAL_MODES.find(m => m.mode === activeMode)?.label}
              </Badge>
            )}
          </div>

          {isLoading ? (
            <Card className="p-12 rounded-xl border-slate-200 shadow-sm text-center flex flex-col items-center">
               <Loader2 className="w-8 h-8 text-blue-600 animate-spin mb-4" />
               <p className="text-slate-600">Querying database...</p>
            </Card>
          ) : !hasSearched ? (
            <Card className="p-12 rounded-xl border-slate-200 shadow-sm text-center">
              <Search className="w-12 h-12 text-slate-300 mx-auto mb-4" />
              <p className="text-slate-600">Enter a keyword and click Search to retrieve files from the database.</p>
            </Card>
          ) : searchResults.length === 0 ? (
            <Card className="p-12 rounded-xl border-slate-200 shadow-sm text-center">
              <Search className="w-12 h-12 text-slate-300 mx-auto mb-4" />
              <p className="text-slate-600">No results found. Try adjusting your filters.</p>
            </Card>
          ) : (
            <div className="space-y-4">
              {/* Replaced 'result: any' with 'result: SearchResult' */}
              {searchResults.map((result: SearchResult, idx: number) => (
                <Card key={result.id || idx} className="p-5 rounded-xl border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                  <div className="flex gap-4">
                    <div className="flex-shrink-0 flex flex-col items-center gap-1">
                      {getFileIcon(result.fileType)}
                      <Badge className={`text-xs ${getFileTypeBadgeColor(result.fileType)} border`}>
                        {result.fileType}
                      </Badge>
                    </div>

                    <div className="flex-1 min-w-0">
                      <div className="flex items-start justify-between mb-2">
                        <div>
                          <h3 className="font-semibold text-slate-900 truncate">{result.fileName}</h3>
                          <p className="text-sm text-slate-700">
                            {result.studentName} <span className="mx-1 text-slate-400">·</span> <span className="text-slate-500">{result.matricNo}</span>
                          </p>
                        </div>
                      </div>

                      <div className="flex flex-wrap gap-x-5 gap-y-1 mb-3 text-xs text-slate-500">
                        <span className="flex items-center gap-1">
                          <span className="font-medium text-slate-600">Group:</span>
                          <Badge variant="outline" className="bg-blue-50 text-blue-700 border-blue-200 text-xs">{result.group}</Badge>
                        </span>
                        {result.phone && <span className="flex items-center gap-1"><Phone className="w-3 h-3" />{result.phone}</span>}
                        {result.uploadDate && <span className="flex items-center gap-1"><Calendar className="w-3 h-3" />{result.uploadDate}</span>}
                        {result.fileSizeMb && (
                          <span className="flex items-center gap-1">
                            <span className="font-medium text-slate-600">Size:</span>
                            {Number(result.fileSizeMb).toFixed(1)} MB
                          </span>
                        )}
                      </div>

                      {result.lifeMotto && (
                        <div className="flex items-start gap-1 mb-3 text-xs text-slate-500 italic">
                          <Quote className="w-3 h-3 mt-0.5 flex-shrink-0" />
                          <span>{result.lifeMotto}</span>
                        </div>
                      )}
                    </div>
                  </div>
                </Card>
              ))}
            </div>
          )}
        </div>

        <div className="space-y-4">
          <Card className="p-5 rounded-xl border-slate-200 shadow-sm">
            <h3 className="font-semibold text-slate-900 mb-4">System Overview</h3>
            <div className="space-y-3">
              {[
                { label: "Total Files Indexed", value: dbStats.totalFiles, color: "text-slate-900" },
                { label: "PDF Documents", value: dbStats.pdfCount, color: "text-red-600" },
                { label: "Audio Files (MP3)", value: dbStats.audioCount, color: "text-orange-600" },
                { label: "Video Files (MP4)", value: dbStats.videoCount, color: "text-blue-600" },
              ].map(({ label, value, color }) => (
                <div key={label} className="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                  <span className="text-sm text-slate-600">{label}</span>
                  <span className={`font-semibold ${color}`}>{value}</span>
                </div>
              ))}
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}