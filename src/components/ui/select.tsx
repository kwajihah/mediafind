import * as React from "react"
import { cn } from "./utils"

interface SelectContextType {
  value?: string;
  onValueChange?: (value: string) => void;
}

const SelectContext = React.createContext<SelectContextType>({});

export function Select({ value, onValueChange, children }: { value?: string, onValueChange?: (value: string) => void, children: React.ReactNode }) {
  return (
    <SelectContext.Provider value={{ value, onValueChange }}>
      <div className="relative w-full">{children}</div>
    </SelectContext.Provider>
  )
}

export function SelectTrigger({ children, className }: { children: React.ReactNode, className?: string }) {
  return (
    <div className={cn("flex h-10 w-full items-center justify-between rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600", className)}>
      {children}
    </div>
  )
}

export function SelectValue({ placeholder }: { placeholder?: string }) {
  const { value } = React.useContext(SelectContext);
  return <span>{value || placeholder}</span>;
}

export function SelectContent({ children }: { children: React.ReactNode }) {
  const { value, onValueChange } = React.useContext(SelectContext);
  return (
    <select 
      value={value} 
      onChange={(e) => onValueChange?.(e.target.value)}
      className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
    >
      {children}
    </select>
  )
}

export function SelectItem({ value, children }: { value: string, children: React.ReactNode }) {
  return <option value={value}>{children}</option>;
}